var low_h = 25;
var low_s = 90;
var low_l = 92;

var high_h = 25;
var high_s = 92;
var high_l = 61;

function get_color(point) {
	h = interpolate(low_h, high_h, point);
	s = interpolate(low_s, high_s, point);
	l = interpolate(low_l, high_l, point);
	return 'hsla('+h+', '+s+'%, '+l+'%, 0.9)';
}

function interpolate(low, high, t) {
	return (1 - t) * low + t * high;
}

function styleRatingConfidence(selector, val){
	var bgColor = get_color(val);
	var style = jQuery('<style>');
	style.appendTo('head');
	var base = selector + ':after {content: "";display: block;width:100%;height:0;padding-bottom:100%;background-color:' 
	+ bgColor + 
	';-moz-border-radius:50%;-webkit-border-radius:50%;border-radius:50%;}';
	style.html(base); 
	return bgColor;
}

function createConfLegend(text) {
	var prefix = '<div class="color-legend" id="grad1">' +
	'<div><div style="float:left;">L</div></div>' +
	'<div><div class="color-legend-label">Rating reliability color legend</div></div>' +
	'<div><div style="float:right">H</div></div></div>' + 
	'<span class="conf-text">';
	var suffix = '</span>';

	return prefix + text + suffix;
}