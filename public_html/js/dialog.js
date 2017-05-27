'use strict';

function Dialog(header, body, footer, eventHandlers, backdropStatic) {
    this._dialog;
    this._closed = false;
    this.onclose;

    var id = Dialog.prototype._idPrefix + Dialog.prototype._id, self;
    Dialog.prototype._id === Number.MAX_SAFE_INTEGER ? 0 : ++Dialog.prototype._id;

    //could be static wc means doesn't disappear  wen d page is clicked
    //z-dialog-
    if (eventHandlers) {
        //eventHandlers id:mouseup,function(){} //prependthe idPrefix and _id to the elem b4 creating and attaching ev handler
        header = header.replace(/z-dialog-[a-z0-9-_]*/g, function (match) {
            return 'id="' + id + match + '"';
        });
        body = body.replace(/z-dialog-[a-z0-9-_]*/g, function (match) {
            return 'id="' + id + match + '"';
        });
        footer = footer.replace(/z-dialog-[a-z0-9-_]*/g, function (match) {
            return 'id="' + id + match + '"';
        });
    }

    $('body').append('<div id="' + id + '" class="modal fade" role="dialog"><div class="modal-dialog"><div class="modal-content">' + (header ? '<div class="modal-header">' + header + '<button type="button" class="close" data-dismiss="modal">&times;</button></div>' : '') + (body ? '<div class="modal-body">' + body + '</div>' : '') + (footer ? '<div class="modal-footer">' + footer + '</div>' : '') + '</div></div></div>');

    self = this;

    this._dialog = $('#' + id).modal(backdropStatic ? {backdrop: 'static', keyboard: false} : {});
    this._dialog.on('hidden.bs.modal', function (e) {
        self.close();
    });

    if (eventHandlers) {
        for (var suffixId in eventHandlers) {
            document.getElementById(id + 'z-dialog-' + suffixId).addEventListener(eventHandlers[suffixId][0], function (e) {
                if (eventHandlers[suffixId][1](e)) {
                    self.close();
                }
            });
        }
    }
}

Dialog.prototype._id = 0;
Dialog.prototype._idPrefix = '_dIaLog';
Dialog.prototype.close = function () {
    if (this._closed) {
        return;
    }

    this._closed = true;
    $('.modal-backdrop').remove();
    //wen an element is deleted the event handlers are deleted too ?
    this._dialog.hide().remove();
    delete this;

    this.onclose && this.onclose();
};