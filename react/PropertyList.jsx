// PropertyList.jsx
// Renders property cards using the EXACT same markup/classes as the original
// static design, but data now comes from api/get_properties.php via fetch (AJAX).
// Filter (gender) and sort buttons dispatch custom window events that this
// component listens to, so no page reload happens.

const { useState, useEffect, useCallback } = React;

function renderStars(rating) {
    const stars = [];
    const full = Math.floor(rating);
    const hasHalf = rating - full >= 0.5;

    for (let i = 0; i < full; i++) {
        stars.push(<i key={'f' + i} className="fas fa-star"></i>);
    }
    if (hasHalf) {
        stars.push(<i key="half" className="fas fa-star-half-alt"></i>);
    }
    const remaining = 5 - full - (hasHalf ? 1 : 0);
    for (let i = 0; i < remaining; i++) {
        stars.push(<i key={'e' + i} className="far fa-star"></i>);
    }
    return stars;
}

function genderIcon(gender) {
    if (gender === 'male') return 'img/male.png';
    if (gender === 'female') return 'img/female.png';
    return 'img/unisex.png';
}

function PropertyCard({ property, onToggleInterest }) {
    return (
        <div className="property-card row">
            <div className="image-container col-md-4">
                <img src={property.thumbnail} />
            </div>
            <div className="content-container col-md-8">
                <div className="row no-gutters justify-content-between">
                    <div className="star-container" title={property.rating}>
                        {renderStars(parseFloat(property.rating))}
                    </div>
                    <div className="interested-container">
                        <i
                            className={`${property.is_interested ? 'fas' : 'far'} fa-heart`}
                            onClick={() => onToggleInterest(property.id)}
                        ></i>
                        <div className="interested-text">{property.interested_count} interested</div>
                    </div>
                </div>
                <div className="detail-container">
                    <div className="property-name">{property.name}</div>
                    <div className="property-address">{property.address}</div>
                    <div className="property-gender">
                        <img src={genderIcon(property.gender)} />
                    </div>
                </div>
                <div className="row no-gutters">
                    <div className="rent-container col-6">
                        <div className="rent">Rs {Number(property.price).toLocaleString('en-IN')}/-</div>
                        <div className="rent-unit">per month</div>
                    </div>
                    <div className="button-container col-6">
                        <a href={`property_detail.php?id=${property.id}`} className="btn btn-primary">View</a>
                    </div>
                </div>
            </div>
        </div>
    );
}

function PropertyList() {
    const rootEl = document.getElementById('react-property-list');
    const initialCity = rootEl ? rootEl.getAttribute('data-city') : '';

    const [properties, setProperties] = useState([]);
    const [gender, setGender] = useState('all');
    const [budget, setBudget] = useState(0);
    const [sort, setSort] = useState('');
    const [interestedMap, setInterestedMap] = useState({});

    const showLoading = (show) => {
        const el = document.getElementById('loading');
        if (el) el.style.display = show ? 'block' : 'none';
    };

    const fetchProperties = useCallback((g, b, s) => {
        showLoading(true);
        const params = new URLSearchParams({ city: initialCity, gender: g, budget: b, sort: s });
        fetch(`api/get_properties.php?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    setProperties(data.properties);
                    const map = {};
                    data.properties.forEach(p => { map[p.id] = p.is_interested == 1; });
                    setInterestedMap(map);
                }
                showLoading(false);
            })
            .catch(() => showLoading(false));
    }, [initialCity]);

    useEffect(() => {
        fetchProperties(gender, budget, sort);

        const genderHandler = (e) => setGender(e.detail);
        const budgetHandler = (e) => setBudget(e.detail);
        const sortHandler = (e) => setSort(e.detail);

        window.addEventListener('pglife:filterGender', genderHandler);
        window.addEventListener('pglife:filterBudget', budgetHandler);
        window.addEventListener('pglife:sort', sortHandler);

        return () => {
            window.removeEventListener('pglife:filterGender', genderHandler);
            window.removeEventListener('pglife:filterBudget', budgetHandler);
            window.removeEventListener('pglife:sort', sortHandler);
        };
    }, []);

    // Refetch whenever gender, budget or sort changes (after the initial mount)
    useEffect(() => {
        fetchProperties(gender, budget, sort);
    }, [gender, budget, sort]);

    const handleToggleInterest = (propertyId) => {
        const formData = new FormData();
        formData.append('property_id', propertyId);

        fetch('api/toggle_interest.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.message === 'please_login') {
                    $('#login-modal').modal('show');
                    return;
                }
                if (data.success) {
                    setProperties(prev => prev.map(p =>
                        p.id === propertyId
                            ? { ...p, is_interested: p.is_interested ? 0 : 1, interested_count: data.interested_count }
                            : p
                    ));
                }
            });
    };

    if (properties.length === 0) {
        return (
            <div className="no-property-container">
                <p>No properties found matching your filters.</p>
            </div>
        );
    }

    return (
        <>
            {properties.map(p => (
                <PropertyCard key={p.id} property={p} onToggleInterest={handleToggleInterest} />
            ))}
        </>
    );
}

const root = ReactDOM.createRoot(document.getElementById('react-property-list'));
root.render(<PropertyList />);
