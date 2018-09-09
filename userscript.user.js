// ==UserScript==
// @name       ExHentai Archive
// @match      *://exhentai.org/*
// @match      *://e-hentai.org/*
// @require    https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js
// ==/UserScript==

var baseUrl = '//your.archive.url.com/';
var key = 'changeme';

var archiver = {
    gallerycount: 1,
    start: function() {
        console.log('archiver.start()');
        // Detect view mode
        if($('div.itg') !== null) {
            this.gallerycount = $('div.itg').children().length-1;
        }
    },
    queue: [],
    galleryids: [],
    addGalleryToQueue: function(galleryid, callbackFunction) {
        this.queue.push({
            id: galleryid,
            callback: callbackFunction
        });
        this.galleryids.push(galleryid);

        // Queue length equals gallerycount. Time to hit it off
        if(this.queue.length == this.gallerycount) {
            this.fire();
        }
    },
    fire: function() {
        $.ajax({
            url: baseUrl+'legacy/',
            method: 'POST',
            data: {
                action: 'hasGalleries',
                gids: this.galleryids,
                key: key
            },
            context: {
                gids: this.galleryids,
                callbackArray: this.queue
            },
            dataType: 'json'
        }).done(function(data){
            this.existingGalleriesOffset = [];
            var that = this;

            data.data.forEach(function(item) {
                var arrayOffset = that.gids.indexOf(item.exhenid);
                var callbackItem = that.callbackArray[arrayOffset];

                item.exists = true;

                callbackItem.callback(item);

                // Add offset to list of items to be deleted
                that.existingGalleriesOffset.push(arrayOffset);
            });

            that.existingGalleriesOffset.forEach(function(offset){
                that.callbackArray.splice(offset, 1);
            });

            that.callbackArray.forEach(function(item) {
                var payload = {exists: false};
                item.callback(payload);
            });
        });

        return;
    }
}

archiver.start();


function createArchiveLink(gid, token) {
    var link = $('<a href="#">DL</a>');
    link.data('gid', gid);
    link.data('token', token);

    link.on('click', function() {
        $.getJSON(baseUrl + 'legacy/', { action: 'addgallery', gid: link.data('gid'), token: link.data('token'), key: key }, function(data, result) {
            if(data.ret === true && result === 'success') {
                $(link).css({
                    color: '#777',
                    pointerEvents: 'none'
                });
            }
            else {
                alert('An error occured while adding to archive');
            }
        });

        return false;
    });

    return link;
}

$('div#gd5').each(function() { //archive button on gallery detail
    var container = $(this);

    $.getJSON(baseUrl + 'legacy/', { action: 'hasgallery', gid: gid, key: key }, function(data, result) {
        if(data.data.exists) {
            var p = $('<p class="g2"><img src="//exhentai.org/img/mr.gif"> </p>');
            var link = "";
            if (data.data.deleted == 0) {
                link = $('<a href="#" target="_blank">Archived</a>');
            } else if (data.data.deleted >= 1) {
                link = $('<a href="#">Deleted</a>');
            }

            if(data.data.archived && data.data.deleted == 0) {
                link.prop('href', baseUrl + '?' + $.param({ action: 'gallery', id: data.data.id }));
            }
            else if (!data.data.archived) {
                link.on('click', function() {
                    alert('Not yet downloaded');
                    return false;
                });
            }

            link.appendTo(p);
            $('.g2', container).last().after(p);
        }
        else {
            var p = $('<p class="g2"><img src="//exhentai.org/img/mr.gif"> </p>');
            var link = createArchiveLink(gid, token);
            link.appendTo(p);

            $('.g2', container).last().after(p);
        }
    });
});

$('div.itg').each(function() { //gallery search
    var container = $(this);
    var galleries = $('div.id1', container);
    var gids = [ ];

    galleries.each(function() {
        var galleryContainer = $(this);
        var link = $('div.id2 a', galleryContainer).prop('href');

        var bits = link.split("/");

        var gid = bits[4];
        var token = bits[5];

        archiver.addGalleryToQueue(gid, function(data){
            if (!data.exists) {
                var link = createArchiveLink(gid, token);
                link.css({ fontSize: '9px' });
                link.on('click', function() {
                    $(this).parents('.id1').css({ background: 'green' });
                });

                link.prependTo($('.id44 div', galleryContainer));
            } else {
                var res = "";
                if (data.archived == 1 && data.deleted == 0) {
                    res = $('<p>Archived</p>');
                    galleryContainer.css({background: 'green'});
                } else if (data.deleted >= 1) {
                    res = $('<p>Deleted</p>');
                    galleryContainer.css({background: '#AA0000'});
                } else if (data.archived == 0) {
                    galleryContainer.css({background: '#ffaa00'})
                }

                if(res !== "")
                    res.prependTo($('.id44 div', galleryContainer));
            }
        });

        galleryContainer.data('gid', gid);
        $('.id44 img', galleryContainer).remove();

        return;
    });
});
