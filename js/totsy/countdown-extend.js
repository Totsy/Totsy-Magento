function getTimerHtml(event, endCount, server_now){
    var saleTime = new Date(endCount);
    var now = new Date(server_now);
    var diff = saleTime.getTime() - now.getTime();
    if (event=='live'){
        if ( (diff/1000) < 60 ) {
            htmlTemplate = "<span class='prefix'>Sale Ends:</span> {snn} Seconds";
        } else if ( (diff/1000) < 3600 ) {
            htmlTemplate = "<span class='prefix'>Sale Ends:</span> {mnn} Minutes, {snn} Seconds";
        } else if ( ((diff/1000) > 3600) && ((diff/1000) < 86400) ) {
            htmlTemplate = "<span class='prefix'>Sale Ends:</span> {hnn} Hours, {mnn} Minutes";
        } else if ( ((diff/1000) > 86400) && ((diff/1000) < 172800) ) {
            htmlTemplate = "<span class='prefix'>Sale Ends:</span> {dn} Day, {hnn} Hours";
        } else {
            htmlTemplate = "<span class='prefix'>Sale Ends:</span> {dn} Days, {hnn} Hours";
        }
    }else if(event=='upcoming'){
        if( (diff/1000) < 86400 ){
            htmlTemplate = "<span>Opens in</span> {hnn}<span class=\"cd-time\">:</span>{mnn}<span class=\"cd-time\">:</span>{snn}<span class=\"cd-time\"></span>";
        }else {
            htmlTemplate = "<span>Opens in</span><span id=\"cd-day\"> {dn}</span> <span class=\"cd-day\">Days</span> {hnn}<span class=\"cd-time\">:</span>{mnn}<span class=\"cd-time\">:</span>{snn}<span class=\"cd-time\"></span>";
        }
    }
    return htmlTemplate;
}

function getEarlyAccessTimerHtml(event, endCount,server_now){
    var saleTime = new Date(endCount);
    var now = new Date(server_now);
    var diff = saleTime.getTime() - now.getTime();
    if (event=='live'){
        if ( (diff/1000) < 60 ) {
            htmlTemplate = "<span class='prefix'></span> {snn} Seconds";
        } else if ( (diff/1000) < 3600 ) {
            htmlTemplate = "<span class='prefix'></span> {mnn} Minutes, {snn} Seconds";
        } else if ( ((diff/1000) > 3600) && ((diff/1000) < 86400) ) {
            htmlTemplate = "<span class='prefix'></span> {hnn} Hours, {mnn} Minutes";
        } else if ( ((diff/1000) > 86400) && ((diff/1000) < 172800) ) {
            htmlTemplate = "<span class='prefix'></span> {dn} Day, {hnn} Hours";
        } else {
            htmlTemplate = "<span class='prefix'></span> {dn} Days, {hnn} Hours";
        }
    }else if(event=='upcoming'){
        if( (diff/1000) < 86400 ){
            htmlTemplate = "{hnn}<span class=\"cd-time\">:</span>{mnn}<span class=\"cd-time\">:</span>{snn}<span class=\"cd-time\"></span>";
        }else {
            htmlTemplate = "<span id=\"cd-day\"> {dn}</span> <span class=\"cd-day\">Days</span> {hnn}<span class=\"cd-time\">:</span>{mnn}<span class=\"cd-time\">:</span>{snn}<span class=\"cd-time\"></span>";
        }
    }else if(event=='earlyaccess') {
        if( (diff/1000) < 86400 ){
            htmlTemplate = "{hnn}<span class=\"cd-time\">h:</span>{mnn}<span class=\"cd-time\">m:</span>{snn}<span class=\"cd-time\">s</span>";
        }else {
            htmlTemplate = "<span id=\"cd-day\"> {dn}</span> <span class=\"cd-day\">Days</span> {hnn}<span class=\"cd-time\">h:</span>{mnn}<span class=\"cd-time\">m:</span>{snn}<span class=\"cd-time\">s</span>";
        }
    }
    return htmlTemplate;
}
