
document.addEventListener('click', function(e) {
    if (!isMontonioCheckoutElement(e)) {
        return;
    };

    var preselectedAspspId = getMontonioPreselectedAspspId(e);
    setMontonioPreselectedAspsp(preselectedAspspId);

    var montonioCheckoutElements = document.querySelectorAll("[data-aspsp]");
    for (var i = 0; i < montonioCheckoutElements.length; i++) {
        montonioCheckoutElements[i].classList.remove('active');
    }
    e.target.classList.add('active');
})

function getMontonioPreselectedAspspId(e) {
    return e.target.getAttribute('data-aspsp');
}

function isMontonioCheckoutElement(e) {
    return e.target.hasAttribute('data-aspsp');
}

function setMontonioPreselectedAspsp(identifier) {
    var preselectedAspspInput = document.getElementById('montonio_payments_preselected_aspsp');
    preselectedAspspInput.value = identifier;
}

document.addEventListener("DOMContentLoaded", function() {
    var isoCountries = [
        { id: 'EE', text: 'Estonia'},
        { id: 'LT', text: 'Lithuania'},
        { id: 'LV', text: 'Latvia'},
        { id: 'FI', text: 'Finland'},
    ];

    document.addEventListener('change', function(e) {
        console.log(e);

        if (e.target.classList.contains('montonio-payments-country-dropdown')) {
            var selectedRegion = e.target.value;

            var aspsps = document.querySelectorAll('.montonio-aspsp');
            for (var i = 0; i < aspsps.length; i++) {
                aspsps[i].classList.add('montonio-hidden');
            }

            var aspsps = document.querySelectorAll('.aspsp-region-'+selectedRegion);
            for (var i = 0; i < aspsps.length; i++) {
                aspsps[i].classList.remove('montonio-hidden');
            }
        }
    })
});
