/* ===========================================================
 * trumbowyg.emoji.js v0.1
 * Emoji picker plugin for Trumbowyg
 * http://alex-d.github.com/Trumbowyg
 * ===========================================================
 * Author : Nicolas Pion
 *          Twitter : @nicolas_pion
 */

(function ($) {
    'use strict';






    var defaultOptions = {
        emojiList: [
            '&#x1F600',
            '&#x1F603',
            '&#x1F604',
            '&#x1F601',
            '&#x1F606',
            '&#x1F605',
            '&#x1F602',
            '&#x1F923',
            '&#x263A',
            '&#x1F60A',
            '&#x1F607',
            '&#x1F642',
            '&#x1F643',
            '&#x1F609',
            '&#x1F60C',
            '&#x1F60D',
            '&#x1F618',
            '&#x1F970',
            '&#x1F617',
            '&#x1F619',
            '&#x1F61A',
            '&#x1F60B',
            '&#x1F61B',
            '&#x1F61D',
            '&#x1F61C',
            '&#x1F92A',
            '&#x1F928',
            '&#x1F9D0',
            '&#x1F913',
            '&#x1F60E',
            '&#x1F929',
            '&#x1F973',
            '&#x1F60F',
            '&#x1F612',
            '&#x1F61E',
            '&#x1F614',
            '&#x1F61F',
            '&#x1F615',
            '&#x1F641',
            '&#x1F623',
            '&#x1F616',
            '&#x1F62B',
            '&#x1F629',
            '&#x1F622',
            '&#x1F62D',
            '&#x1F624',
            '&#x1F620',
            '&#x1F621',
            '&#x1F92C',
            '&#x1F92F',
            '&#x1F633',
            '&#x1F631',
            '&#x1F628',
            '&#x1F630',
            '&#x1F975',
            '&#x1F976',
            '&#x1F97A',
            '&#x1F625',
            '&#x1F613',
            '&#x1F917',
            '&#x1F914',
            '&#x1F92D',
            '&#x1F92B',
            '&#x1F925',
            '&#x1F636',
            '&#x1F610',
            '&#x1F611',
            '&#x1F62C',
            '&#x1F644',
            '&#x1F62F',
            '&#x1F626',
            '&#x1F627',
            '&#x1F62E',
            '&#x1F632',
            '&#x1F634',
            '&#x1F924',
            '&#x1F62A',
            '&#x1F635',
            '&#x1F910',
            '&#x1F974',
            '&#x1F922',
            '&#x1F92E',
            '&#x1F927',
            '&#x1F637',
            '&#x1F912',
            '&#x1F915',
            '&#x1F911',
            '&#x1F920',
            '&#x1F608',
            '&#x1F47F',
            '&#x1F479',
            '&#x1F47A',
            '&#x1F921',
            '&#x1F4A9',
            '&#x1F47B',
            '&#x1F480',
            '&#x1F47D',
            '&#x1F47E',
            '&#x1F916',
            '&#x1F383',
            '&#x1F63A',
            '&#x1F638',
            '&#x1F639',
            '&#x1F63B',
            '&#x1F63C',
            '&#x1F63D',
            '&#x1F640',
            '&#x1F63F',
            '&#x1F63E',
            '&#x1F932',
            '&#x1F450',
            '&#x1F64C',
            '&#x1F44F',
            '&#x1F91D',
            '&#x1F44D',
            '&#x1F44E',
            '&#x1F44A',
            '&#x270A',
            '&#x1F91B',
            '&#x1F91C',
            '&#x1F91E',
            '&#x270C',
            '&#x1F91F',
            '&#x1F918',
            '&#x1F44C',
            '&#x1F448',
            '&#x1F449',
            '&#x1F446',
            '&#x1F447',
            '&#x261D',
            '&#x270B',
            '&#x1F91A',
            '&#x1F590',
            '&#x1F596',
            '&#x1F44B',
            '&#x1F919',
            '&#x1F4AA',
            '&#x1F9B5',
            '&#x1F9B6',
            '&#x1F595',
            '&#x270D',
            '&#x1F64F',
            '&#x1F48D',
            '&#x1F484',
            '&#x1F48B',
            '&#x1F444',
            '&#x1F445',
            '&#x1F442',
            '&#x1F443',
            '&#x1F463',
            '&#x1F440',
            '&#x1F9E0',
            '&#x1F9B4',
            '&#x1F9B7',
            '&#x1F5E3',
            '&#x1F464',
            '&#x1F465',
            '&#x1F476',
            '&#x1F467',
            '&#x1F9D2',
            '&#x1F466',
            '&#x1F469',
            '&#x1F9D1',
            '&#x1F468',
            '&#x1F471',
            '&#x1F9D4',
            '&#x1F475',
            '&#x1F9D3',
            '&#x1F474',
            '&#x1F472',
            '&#x1F473',
            '&#x1F9D5',
            '&#x1F46E',
            '&#x1F477',
            '&#x1F482',
            '&#x1F575',
            '&#x1F470',
            '&#x1F935',
            '&#x1F478',
            '&#x1F934',
            '&#x1F936',
            '&#x1F385',
            '&#x1F9B8',
            '&#x1F9B9',
            '&#x1F9D9',
            '&#x1F9DD',
            '&#x1F9DB',
            '&#x1F9DF',
            '&#x1F9DE',
            '&#x1F9DC',
            '&#x1F9DA',
            '&#x1F47C',
            '&#x1F930',
            '&#x1F931',
            '&#x1F647',
            '&#x1F481',
            '&#x1F645',
            '&#x1F646',
            '&#x1F64B',
            '&#x1F926',
            '&#x1F937',
            '&#x1F64E',
            '&#x1F64D',
            '&#x1F487',
            '&#x1F486',
            '&#x1F9D6',
            '&#x1F485',
            '&#x1F933',
            '&#x1F483',
            '&#x1F57A',
            '&#x1F46F',
            '&#x1F574',
            '&#x1F6B6',
            '&#x1F3C3',
            '&#x1F46B',
            '&#x1F46D',
            '&#x1F46C',
            '&#x1F491',
            '&#x1F48F',
            '&#x1F46A',
            '&#x1F9E5',
            '&#x1F45A',
            '&#x1F455',
            '&#x1F456',
            '&#x1F454',
            '&#x1F457',
            '&#x1F459',
            '&#x1F458',
            '&#x1F97C',
            '&#x1F460',
            '&#x1F461',
            '&#x1F462',
            '&#x1F45E',
            '&#x1F45F',
            '&#x1F97E',
            '&#x1F97F',
            '&#x1F9E6',
            '&#x1F9E4',
            '&#x1F9E3',
            '&#x1F3A9',
            '&#x1F9E2',
            '&#x1F452',
            '&#x1F393',
            '&#x26D1',
            '&#x1F451',
            '&#x1F45D',
            '&#x1F45B',
            '&#x1F45C',
            '&#x1F4BC',
            '&#x1F392',
            '&#x1F453',
            '&#x1F576',
            '&#x1F97D',
            '&#x1F302'
        ]
    };

    // Add all emoji in a dropdown
    $.extend(true, $.trumbowyg, {
        langs: {
            en: {
                emoji: 'emoji'
            },
        },
        // jshint camelcase:true
        plugins: {
            emoji: {
                init: function (trumbowyg) {
                    trumbowyg.o.plugins.emoji = trumbowyg.o.plugins.emoji || defaultOptions;
                    var emojiBtnDef = {
                        dropdown: buildDropdown(trumbowyg)
                    };
                    trumbowyg.addBtnDef('emoji', emojiBtnDef);
                }
            }
        }
    });

    function buildDropdown(trumbowyg) {
        var dropdown = [];

        $.each(trumbowyg.o.plugins.emoji.emojiList, function (i, emoji) {
            if ($.isArray(emoji)) { // Custom emoji behaviour
                var emojiCode = emoji[0],
                    emojiUrl = emoji[1],
                    emojiHtml = '<img src="' + emojiUrl + '" alt="' + emojiCode + '">',
                    customEmojiBtnName = 'emoji-' + emojiCode.replace(/:/g, ''),
                    customEmojiBtnDef = {
                        hasIcon: false,
                        text: emojiHtml,
                        fn: function () {
                            trumbowyg.execCmd('insertImage', emojiUrl, false, true);
                            return true;
                        }
                    };

                trumbowyg.addBtnDef(customEmojiBtnName, customEmojiBtnDef);
                dropdown.push(customEmojiBtnName);
            } else { // Default behaviour
                var btn = emoji.replace(/:/g, ''),
                    defaultEmojiBtnName = 'emoji-' + btn,
                    defaultEmojiBtnDef = {
                        text: emoji,
                        fn: function () {
                            var encodedEmoji = String.fromCodePoint(emoji.replace('&#', '0'));
                            trumbowyg.execCmd('insertText', encodedEmoji);
                            return true;
                        }
                    };

                trumbowyg.addBtnDef(defaultEmojiBtnName, defaultEmojiBtnDef);
                dropdown.push(defaultEmojiBtnName);
            }
        });

        return dropdown;
    }
})(jQuery);
