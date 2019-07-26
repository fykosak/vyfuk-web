jQuery(function () {
    "use strict";
    let $ = jQuery;
    document.querySelectorAll('.task-repo.batch-select').forEach((container) => {
        container.querySelector('select').addEventListener('change', (event) => {
            const year = +event.target.value;
            container.querySelectorAll('.year').forEach((element) => {
                if ((+element.dataset['year']) === year) {
                    element.style.display = '';
                } else {
                    element.style.display = 'none';
                }
            });
        });
    });

    document.querySelectorAll('.figures').forEach((element) => {
        const $figureContainer = $(element);
        let maxIndex = 0;

        const addRow = (index, path = '', cation = '') => {
            maxIndex = index;
            return '<div class="row mb-2">' +
                '<div class="col-6"><input type="text" class="form-control" name="problem[figures][' + index + '][path]" value="' + path + '"/></div>' +
                '<div class="col-6"><input type="text" class="form-control" name="problem[figures][' + index + '][caption]" value="' + cation + '"/></div>' +
                '</div>';
        };

        let html = JSON.parse(element.getAttribute('data-value')).map((figure, index) => {
            return addRow(index, figure.path, figure.caption);
        }).join('');

        html = '<div class="row">' +
            '<div class="col-6">Cesta</div>' +
            '<div class="col-6">Popisek</div>' +
            '</div>' + html;

        element.innerHTML += html;

        $(element).on('input', '', function(){
            let hasValue = false;
            $figureContainer.find('.row').last().find('input').each(function () {
                hasValue = hasValue || (!!$(this).val());
            });
            if (hasValue) {
                $figureContainer.append(addRow(maxIndex + 1));
            }
        });

        element.innerHTML += (addRow(maxIndex + 1));
    });

    const addMediaEl = document.getElementById('addmedia');
    if(addMediaEl){
        addMediaEl.addEventListener('click',(event)=>{
            window.DWMediaSelector.execute((url) => {
                $('.figures .row:last input:first').val(url).trigger('input');
            }, addMediaEl.getAttribute('data-folder-id'));
        });
    }

    $('.dwmediaselector-open').click((event)=>{
        window.DWMediaSelector.execute(null, event.target.getAttribute('data-media-path'));
        return false;
    });
});
