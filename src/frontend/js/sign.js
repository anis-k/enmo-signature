/*
 * Signature Plugin
 * HTML5 Canvas Jquery plugin
 *
 * Examples and documentation at: http://tiendasdigitales.net
 *
 * Copyright (c) 2018 Lucas Gabriel Martinez
 *
 * Version: 1.0.0 - 2018/07/24
 * Requires: jQuery v1.3+
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */
jQuery(document).ready(function (e) {
    jQuery.fn.sign = function (options) {
        var params = jQuery.fn.extend({
            mode: options.mode ? options.mode : 'direct',
            reset: options.resetButton ? options.resetButton : null,
            changeColor: options.changeColor ? options.changeColor : null,
            exportPng: options.exportPng ? options.exportPng : null,
            undo: options.undo ? options.undo : null,
            width: options.width ? options.width : 500,
            height: options.height ? options.height : 300,
            fixWidth: options.fixWidth ? options.fixWidth : 0,
            fixHeight: options.fixHeight ? options.fixHeight : 0,
            lineWidth: options.lineWidth ? options.lineWidth : 10,
            mobileMode: options.mobileMode ? options.mobileMode : false,
        }, options);

        var canvas = jQuery(this);

        var lineWidth = params.lineWidth;

        var context = canvas.get(0).getContext('2d');

        var fixFingerPosition = 15;

        canvas.attr("width", params.width);
        canvas.attr("height", params.height);

        var points = [];
        var last = {
            x: null,
            y: null
        };
        var holdClick = false;

        var getMousePosition = function (canvas, evt) {
            var touch= {};
			if(evt.touches === undefined){
                touch = { clientX : evt.clientX, clientY : evt.clientY };
            } else {
                touch = evt.touches[0];
            }
            return {
                x: touch.clientX - params.fixWidth,
                y: touch.clientY - params.fixHeight
            };
        }

        var draw = function (ctx, x, y) {
            points.push({
                x: x,
                y: y,
                break: false
            });
            ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
            var p1 = points[0];
            var p2 = points[1];

            ctx.beginPath();
            ctx.moveTo(p1.x, p1.y);

            for (var i = 0; i < points.length; i++) {
                
                var midPoint = calculateMiddlePoint(p1, p2);
                if (p1.break) {
                    ctx.moveTo(p2.x, p2.y);
                } else {
                    ctx.quadraticCurveTo(p1.x, p1.y, midPoint.x, midPoint.y);
                }
                p1 = points[i];
                p2 = points[i + 1];
            }

            ctx.lineWidth = lineWidth;
            //ctx.lineTo(p1.x, p1.y);
            ctx.stroke();
        }

        var calculateMiddlePoint = function (pointStart, pointEnd) {
            return {
                x: pointStart.x + (pointEnd.x - pointStart.x) / 2,
                y: pointStart.y + (pointEnd.y - pointStart.y) / 2
            }
        }

        var detectMode = function (mode, e) {
            if (mode === 'stylus') {
                if (e.touches === undefined) {
                    return false
                } else if (e.touches[0].touchType === 'stylus') {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }


        // Mouse & touch events
        canvas.on('touchstart mousedown', function (e) {
            if (detectMode(params.mode, e)) {
                holdClick = true;
                var mousePosition = getMousePosition(canvas, e);
                points.push({
                    x: mousePosition.x,
                    y: mousePosition.y,
                    break: false
                });

                canvas.on('touchmove mousemove', function (e) {

                    if (holdClick) {
                        var mousePosition = getMousePosition(canvas, e);
                        draw(context, mousePosition.x, mousePosition.y);
                    }
    
                    return false;
                });
            }
            
            return false;
        }).on('touchend mouseup', function (e) {
            e.preventDefault();
            holdClick = false;
            if (points.length > 0) {
                points[points.length - 1].break = true;
            }
            canvas.unbind('mousemove');
            canvas.unbind('touchmove');
            return false;
        });

        // Reset canvas
        var reset = function () {
            context.clearRect(0, 0, canvas.width(), canvas.height());
            points.length = 0;
        }

        if (params.reset !== null) {
            params.reset.on('click touchend', function () {
                reset();
            });
        }

        // Change color canvas
        var changeColor = function (color) {
            context.strokeStyle = color;
        }

        if (params.changeColor !== null) {
            params.changeColor.on('click touchend', function (e) {
                changeColor(e.target.value);
            });
        }

        // Change color canvas
        var exportPng = function () {
            var imgData = canvas[0].toDataURL('image/png');
        }

        if (params.exportPng !== null) {
            params.exportPng.on('click touchend', function () {
                exportPng();
            });
        }

        var undo = function () {
            var new_points = [];
            var grp_new_points = [];
            for (var index = 0; index < points.length; index++) {
                if(points[index].break === true) {
                    new_points.push(points[index]);
                    grp_new_points.push(new_points);
                    new_points = [];
                } else {
                    new_points.push(points[index]);
                }
            }
            grp_new_points.pop();
            points = [];
            for (let index = 0; index < grp_new_points.length; index++) {
                points = points.concat(grp_new_points[index])
                
            }

            if (points.length == 0) {
                reset();
            } else {
                context.clearRect(0, 0, context.canvas.width, context.canvas.height);
                var p1 = points[0];
                var p2 = points[1];
    
                context.beginPath();
                context.moveTo(p1.x, p1.y);
    
                for (var i = 1; i < points.length; i++) {
                    var midPoint = calculateMiddlePoint(p1, p2);
                    if (p1.break) {
                        context.moveTo(p2.x, p2.y);
                    } else {
                        context.quadraticCurveTo(p1.x, p1.y, midPoint.x, midPoint.y);
                    }
                    p1 = points[i];
                    p2 = points[i + 1];
                }
    
                context.lineWidth = lineWidth;
                context.lineTo(p1.x, p1.y);
                context.stroke();
                points[points.length - 1].break = true;
            } 
        }

        if (params.undo !== null) {
            params.undo.on('click touchend', function () {
                undo();
            });
        }
    };
});