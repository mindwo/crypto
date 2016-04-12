window.DxCalendar = window.DxCalendar || {
    /**
     * Bloka unikālais identifikators
     */
    block_guid: '',
    /**
     * Notikumi JSON string formātā
     */
    events_items: '',
    /**
     * Uzzīmē kalendāra komponenti
     * @returns {undefined}
     */
    renderCalendar: function() {
        $('#dailyquest-' + window.DxCalendar.block_guid + '-calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            lang: 'lv',
            buttonIcons: false, // show the prev/next text
            weekNumbers: false,
            editable: false,
            eventLimit: true, // allow "more" link when too many events
            allDaySlot: false,
            events: window.DxCalendar.events_items
        });
    },
    /**
     * Uzstāda galeriju bloka parametrus
     * @param {object} block Galerijas bloka elements
     * @returns {undefined}
     */
    setParameters: function(block) {
        window.DxCalendar.block_guid = block.attr('dx_block_guid');

        var evenets_raw = JSON.parse(block.attr('dx_events_items'));

        window.DxCalendar.events_items = [];

        for (var key in evenets_raw) {
            window.DxCalendar.events_items.push({
                title: evenets_raw[key].title,
                start: evenets_raw[key].start,
                end: evenets_raw[key].end,
                url: 'JavaScript: get_popup_item_by_id(' + evenets_raw[key].id + ', "event", "Kalendāra notikums");',
                color: evenets_raw[key].color
            });
        }
    },
    /**
     * Inicializē kalendāra komponenti
     * @returns {undefined}
     */
    init: function() {
        // Iegūst kalendāra bloku
        $(".dx-block-container-calendar[dx_block_init='0']").each(function() {
            // Iegūst parametrus
            window.DxCalendar.setParameters($(this));

            // Uzzīmē kalendāru
            window.DxCalendar.renderCalendar();

            // Uzstāda pazīmi ka bloks ir inicializēts
            $(this).attr('dx_block_init', 1);
        });
    }
};

$(document).ready(function() {
    window.DxCalendar.init();
});

