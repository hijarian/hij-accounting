/**
 * Created by hijarian on 22.11.13.
 */

$(document).foundation();
console.log('Ready to work.');

//------------------------------------------------------------------------------
// Добавление новой строчки для ввода трат BEGIN
var lastParent;

function countEmptyRows(rows) {
    var i, row, count;

    function notEmptyVal() {
        return $(this).val() !== '';
    }

    count = 0;
    for (i = 0; i < rows.length; ++i) {
        row = $(rows[i]);
        if (row.find('input[type=text]').filter(notEmptyVal).length === 0) {
            ++count;
        }
    }
    return count;
}

function addBlankRow(rows) {
    var blueprint = $(rows[0]).clone();
    blueprint.find('input').each(function (i, elem) {
        $(elem).val('');
    });
    blueprint.find('.cost').text('');
    rows.parent().append(blueprint);
}

/**
 * @param event
 */
var reloadInputRows = function (event) {
    var target = $(event.target);

    var parent = target.parents('tr');
    if (parent == lastParent)
        return;
    lastParent = parent;

    var rows = target.parents('tbody').find('tr');

    if (!countEmptyRows(rows))
        addBlankRow(rows);
};

$('#add-check-form table').on('blur', 'input,select', reloadInputRows);

// Добавление новой строчки для ввода трат END
//------------------------------------------------------------------------------
// Обновление стоимости BEGIN

accounting.settings.currency.format = "%v %s";
accounting.settings.currency.symbol = "руб.";
accounting.settings.currency.thousand = " ";

function refreshCost(event)
{
    console.debug('refresh!');

    function getFloatVal(row, selector) {
        return row.find(selector).val().replace(',', '.');
    }

    function updateCost(row, price, discount, amount) {
        var elem = row.find('.cost-display-cell .cost'),
            mult = 100,
            cost = (parseInt(price * mult) - parseInt(discount * mult)) * parseFloat(amount) / mult,
            costFormatted = accounting.formatMoney(cost);

        elem.data('value', cost);
        elem.text(costFormatted);
    }

    function updateTotal(table) {
        var costElems = table.find('.cost-display-cell .cost'),
            totalElem = table.find('tfoot .total .total-value'),
            total = calcTotal(costElems),
            totalFormatted = accounting.formatMoney(total);

        totalElem.text(totalFormatted);
    }

    function calcTotal(costElements) {
        return _.reduce(
            $.map(
                costElements,
                function (el) { return $(el).data('value'); }
            ),
            function (memo, value) { return memo + value},
            0
        );
    }

    var target = $(event.target),
        row = target.parents('tr'),
        table = row.parents('table'),
        price = getFloatVal(row, '.price-input-cell input'),
        discount = getFloatVal(row, '.discount-input-cell input'),
        amount = getFloatVal(row, '.amount-input-cell input');

    console.debug(price);
    console.debug(discount);
    console.debug(amount);

    updateCost(row, price, discount, amount);
    updateTotal(table);
}

$('#add-check-form table').on('blur', '.amount-input,.price-input,.discount-input', refreshCost);
// Обновление стоимости END
//------------------------------------------------------------------------------
// Исправление тупанского поведения табов BEGIN

function changeHash (event) {
    window.location.hash = $(event.target).attr('href');
}

function toggleActiveTab (event) {
    $($(event.target).attr('href')).parent().slideToggle();
}

$('dl.tabs')
    .on('click', 'dd a', changeHash)
    .on('click', 'dd.active a', toggleActiveTab);

function openFilter () {
    if (window.location.hash == '#filter') {
        $('dl.tabs dd a[href="#filter"]').click();
    }
}

openFilter();
// Исправление тупанского поведения табов END
//------------------------------------------------------------------------------
// Datepicker BEGIN
$('.datepicker').pikaday({
    firstDay: 1,
    format: 'YYYY-MM-DD',
    i18n: {
        previousMonth : 'Месяцем ранее',
        nextMonth     : 'Месяцем позже',
        months        : ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
        weekdays      : ['Воскресенье','Понедельник','Вторник','Среда','Четверг','Пятница','Суббота'],
        weekdaysShort : ['Вс','Пн','Вт','Ср','Чт','Пт','Сб']
    }
});
