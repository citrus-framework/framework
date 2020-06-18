/**
 * citrus faces js
 *
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 */

(function(jQuery) {
    jQuery.extend({
        objectSize: function(object){
            var count = 0;
            $.each(object, function(){
                count++;
            });
            return count;
        },
        numberFormat: function(_options){
            var options = jQuery.extend(true, {
                number: 0,
                decimals: 0
            }, _options);
            
            var number = options.number;
            
            // remove ','
            number = String(number);
            number = number.replace(/,/g, '');
            
            // parse float
            number = parseFloat(number);
            
            // infinity
            number = String(number);
            
            // parse '123456'.'789'
            var numbers = number.split('.');
            
            // add ','
            numbers[0] = numbers[0].replace(/(\d{1,3})(?=(\d{3})+(?!\d))/g, "$1,");
            
            // concat
            number = numbers[0];
            if(numbers[1] !== undefined) {
                number += '.' + numbers[1];
            }
            
            return number;
        },
        uniqueID: function(_options){
            var options = jQuery.extend(true, {
                length: 16,
                prefix: '',
                suffix: '',
                retry: 16
            }, _options);

            var wk = 0;
            var result = '';
            for (var i = 0; i < options.retry; i++) {
                while (result.length < options.length) {
                    wk = Math.floor(Math.random() * 36);
                    if ((wk - 10) < 0) {
                        result += (wk + '');
                    }
                    else {
                        result += String.fromCharCode((wk - 10) + 65);
                    }
                }
                if ($('#' + result).length > 0) {
                    result = '';
                }
                else {
                    break;
                }
            }
            return options.prefix + result + options.suffix;
        },
        sizeFormat: function(_options){
            var options = jQuery.extend(true, {
                byte: 0,
                suffix: true,
                format: 'auto'
            }, _options);

            var byte = parseFloat(options.byte);
            var format = options.format;
            var optimize = byte;

            if (format === 'B') {
                optimize = byte;
            } else if (format === 'KB') {
                optimize = (byte / 1024);
            } else if (format === 'MB') {
                optimize = (byte / (1024 * 1024));
            } else if (format === 'auto') {
                // B
                format = 'B';
                // KB
                if (optimize > 1024) {
                    optimize = (optimize / 1024);
                    format = 'KB';
                }
                // MB
                if (optimize > 1024) {
                    optimize = (optimize / 1024);
                    format = 'MB';
                }
            }

            var result = Math.round(optimize * 10) / 10;
            if (options.suffix === true) {
                result = result + ' ' + format;
            }
            return result;
        },
        percentFormat: function(_options){
            var options = jQuery.extend(true, {
                numerator: 0,
                denominator: 0
            }, _options);

            var percent = (options.numerator / options.denominator) * 100;
            var result = Math.round(percent * 10) / 10;
            result = result + ' %';
            return result;
        },
        // 日数フォーマット
        dayFormat: function(_options){
            var options = jQuery.extend(true, {
                second: 0,
                format: 'auto'
            }, _options);

            var second = options.second;
            var minute = second / 60;
            var hour = minute / 60;
            var day = hour / 24;
            second = parseInt('' + (second % 60));
            minute = parseInt('' + (minute % 60));
            hour = parseInt('' + (hour % 24));
            day = parseInt(day);

            var format = options.format;
            if (format === 'auto') {
                format = '%d days %H:%i:%s';
                if (day === 0) {
                    format = '%H:%i:%s';
                    if (hour === 0) {
                        format = '%i:%s';
                        if (minute === 0) {
                            format = '%s';
                        }
                    }
                }
            }

            return format
                .replace('%d', day)
                .replace('%H', ('00' + hour).slice(-2))
                .replace('%i', ('00' + minute).slice(-2))
                .replace('%s', ('00' + second).slice(-2));
        },
        // 日付フォーマット
        dateFormat: function(_options) {
            var options = jQuery.extend(true, {
                date: new Date(),
                format: 'Y-m-d H:i:s'
            }, _options);

            var date = options.date;
            var result = options.format;
            result = result.replace(/Y/g, date.getFullYear());
            result = result.replace(/m/g, ('0' + (date.getMonth() + 1)).slice(-2));
            result = result.replace(/d/g, ('0' + date.getDate()).slice(-2));
            result = result.replace(/H/g, ('0' + date.getHours()).slice(-2));
            result = result.replace(/i/g, ('0' + date.getMinutes()).slice(-2));
            result = result.replace(/s/g, ('0' + date.getSeconds()).slice(-2));
            return result;
        },
        escapeText: function(text){
            return text
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;')
                ;
        }
    });
    $.fn.attrs = function() {
        var result = {};
        $.each(this.get(0).attributes, function () {
            result[this.name] = this.value;
        });
        return result;
    }
})(jQuery);

if (String.prototype.replaceAll === undefined) {
    String.prototype.replaceAll = function (org, dest) {
        return this.split(org).join(dest);
    };
}
if (String.prototype.format === undefined) {
    String.prototype.format = function(arg) {
        var replace_function = undefined;
        if (typeof arg === 'object') {
            replace_function = function(m, k) { return arg[k]; }
        }
        else {
            var args = arguments;
            replace_function = function(m, k) { return args[ parseInt(k) ]; }
        }

        return this.replace( /\{(\w+)\}/g, replace_function );
    };
}