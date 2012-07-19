/**
 *
 */
({
    events: {
    },
    mapOptions: {
        zoom: 13,
        address_fields: ['address','location']
    },
    _init: function () {
        var self = this;
        if(typeof google != "undefined" && typeof google.load == 'function') {
            google.load("maps", "3", {
                other_params:'sensor=false',
                callback: function(){
                    self.apiLoaded = true;

                    self.getData();
                }
            });
        } else {

            $.ajax({
                url: 'https://www.google.com/jsapi',
                dataType: 'script',
                success: function () {

                    self._init();
                }
            });
        }
    },

    getData: function() {
        console.log('hellooooo');

        var self = this;
        var address;
        //Load configure meta from modules/{Module}/metadata/base/views/googlemap.php
        //Otherwise it loads the mapOption variables as default
        for(var key in this.meta) {
            if(self.mapOptions[key]) {
                self.mapOptions[key] = self.meta[key];
            }
        }
        // loop through possible address fields
        for (var key in self.mapOptions.address_fields)
        {
            // if array of fields (street, city, state, zip),
            // piece fields together into an address
            if(self.mapOptions.address_fields[key] instanceof Array)
            {
                var addr_part;
                address = [];
                for (var addr_key in self.mapOptions.address_fields[key])
                {
                    addr_part = this.model.get(self.mapOptions.address_fields[key][addr_key]);
                    // skip empty fields
                    if(addr_part)
                    {
                        address.push(addr_part);
                    }
                }
                // join together parts with CSV string
                address = address.join(', ');
            }
            else
            {
                // no array, just use as field name
                address = this.model.get(self.mapOptions.address_fields[key]);
            }
            // if we found a valid address, we are done
            if(address)
                break;
        }
        if(address) {
            if(this.apiLoaded) {
                // geocode the address into lat/lon, render
                this.geocoder = this.geocoder || new google.maps.Geocoder();
                this.geocoder.geocode({
                    'address': address
                }, function(results, status) {
                    console.log(results);

                    if (status == google.maps.GeocoderStatus.OK) {

                        self.renderHtml(results);
                    }
                });
            } else {
                this._init();
            }
        }

    },

    renderHtml: function(results) {
        this.$("#map_panel .title").text(results[0].formatted_address);
        this.$('#map_panel').show();
        if(this.map) {
            this.map.setCenter(results[0].geometry.location);
        } else {
            this.mapOptions['center'] = results[0].geometry.location;
            this.mapOptions['mapTypeId'] =  this.mapOptions.mapTypeId || google.maps.MapTypeId.ROADMAP;

            this.map = new google.maps.Map(this.$("#map_canvas")[0], this.mapOptions);
        }
        var marker = new google.maps.Marker({
            map: this.map,
            position: results[0].geometry.location
        });
    },

    resetClock: function(offset, el) {
        // First stop the existing clock
        if (this.timer) {
            clearInterval(this.timer);
        }

        if (offset) {
            this.startClock(offset, el);
            this.timer = setInterval(_.bind(function() { this.startClock(offset, el); }, this), 30000);
        }
    },

    startClock: function(offset, el) {
        var currentTime = new Date();
        var meridian = "am";
        var currentHours = currentTime.getUTCHours();
        var currentMinutes = currentTime.getUTCMinutes();

        currentHours += offset;
        if (currentHours > 24) {
            currentHours -= 24;
        } else if (currentHours < 0) {
            currentHours += 24;
        }

        currentMinutes = (currentMinutes < 10 ? "0" : "") + currentMinutes;
        meridian = (currentHours > 12) ? "pm" : "am";
        currentHours = (currentHours > 12) ? currentHours - 12 : currentHours;

        var time = currentHours + ":" + currentMinutes + meridian;

        this.$(el).html(time);
    },

    bindDataChange: function() {
        var self = this;
        if (this.model) {
            this.model.on("change", function() {
                self.getData();
            }, this);
        }
    }
})
