'use strict';

window.Parsley
        .addValidator('filemaxmegabytes', {
            requirementType: 'string',
            validateString: function (value, requirement, parsleyInstance) {

                if (!('FormData' in window)) {
                    return true;
                }

                var files = parsleyInstance.$element[0].files, maxBytes = requirement * 1048576, err = false;

                for (var i = 0; i < files.length; i++) {
                    // get item
                    if (files.item(i).size > maxBytes) {
                        err = true;
                        break;
                    }
                }

                return !err;
            },
            messages: {
                en: 'File is to big, max size 2MB'
            }
        })
        .addValidator('filemimetypes', {
            requirementType: 'string',
            validateString: function (value, requirement, parsleyInstance) {

                if (!('FormData' in window)) {
                    return true;
                }

                var file = parsleyInstance.$element[0].files;

                if (file.length == 0) {
                    return true;
                }

                var allowedMimeTypes = requirement.replace(/\s/g, "").split(',');
                return allowedMimeTypes.indexOf(file[0].type) !== -1;

            },
            messages: {
                en: 'Invalid file type'
            }
        });