$(function() {
    $('.ui.small.legend.modal')
        .modal('attach events', '.legend.button', 'show')
    ;
    $('.ui.sortable.table').tablesort();
});