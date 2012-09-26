
function UnirgySortable(options) {
    if (!options.container) return;

    var container = $(options.container);
    container.setStyle({position:'static'});
    var state, curEl, tag = options.tag || 'li', filler, fillerPos;
    var mx, my, o, ox, oy, ow, oh, offset;

    $(document).observe('mousemove', move);
    $(document).observe('mouseup', drop);

    function move(ev) {
        if (state=='mousedown') {
            options.ondrag && options.ondrag();
            state = 'dragging';
            curEl.setStyle({position:'absolute', width:ow+'px', height:oh+'px', opacity:.8});
            filler = document.createElement(tag);
            $(filler).setStyle({height:oh+'px'});
        } else if (state!='dragging') {
            return;
        }
        var nx = Event.pointerX(ev), ny = Event.pointerY(ev);

        var sy = document.viewport.getScrollOffsets()[1], so = 0, hh = 50;
        if (ny-sy<hh) {
            so = ny-sy-hh;
        } else {
            var vph = document.viewport.getDimensions().height;
            if (ny-sy>vph-hh) {
                so = ny-sy-(vph-hh);
            }
        }
        if (so) window.scrollBy(0, so);

        //ox += nx-mx;
        oy += ny-my;
        mx = nx;
        my = ny;
        curEl.setStyle({left:ox+'px', top:oy+'px'});

        var els = container.select(tag), i, el;
        for (i=0; i<els.length; i++) {
            if (filler && els[i].offsetTop==filler.offsetTop || els[i].offsetTop==curEl.offsetTop) continue;
            if (els[i].offsetTop>curEl.offsetTop-offset.top) break;
            el = els[i];
        }
        if (el) {
            Element.insert(el, {after:filler});
        } else {
            Element.insert(container, {top:filler});
        }
    }

    function drop(ev) {
        if (!curEl) return;
        if (state=='dragging') {
            curEl.setStyle({position:'', left:'', top:'', width:'', height:'', opacity:1});
            Element.insert(filler, {after:curEl});
            $(filler).remove();
            options.ondrop && options.ondrop();
        }
        filler = null;
        state = null;
        curEl = null;
    }

    return {
        drag: function(ev, el) {
            Event.stop(ev);
            state = 'mousedown';
            curEl = el.tagName==tag ? el : $(el).up(tag);
            mx = Event.pointerX(ev);
            my = Event.pointerY(ev);
            o = $(curEl).positionedOffset();
            ox = o.left;
            oy = o.top;
            o = $(curEl).getDimensions();
            ow = o.width;
            oh = o.height;
            offset = container.positionedOffset();
        }
    }
}