
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

jQuery(document).ready(function() {
    var isoCountries = [
        { id: 'EE', text: 'Estonia'},
        { id: 'LT', text: 'Lithuania'},
        { id: 'LV', text: 'Latvia'},
        { id: 'FI', text: 'Finland'},
    ];

    jQuery(document).on('change', '.montonio-payments-country-dropdown', function (e) {
        var selectedRegion = this.value;

        jQuery('.montonio-aspsp').addClass('montonio-hidden');
        jQuery('.aspsp-region-'+selectedRegion).removeClass('montonio-hidden');
    })
})
