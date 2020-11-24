window.setInterval(function () {

    jQuery.ajax(location.href, {
        dataType: 'html'
    })
    .done(function (html) {
        html = jQuery.parseHTML(html);
        html.forEach(function (node) {
            if (node.className && node.className == 'page-wrapper') {
                var newEl, currentEl;
                if ((newEl = node.querySelector('.form-fields')) && (currentEl = document.querySelector('.form-fields'))) {
                    currentEl.innerHTML = newEl.innerHTML;
                }
            }
        });
    });

}, 1000 * parseInt(document.querySelector('[data-refreshinterval]').getAttribute('data-refreshinterval')));