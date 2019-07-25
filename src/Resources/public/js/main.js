// Load the application once the DOM is ready
$(function () {

    // Initialize templates
    const templates = {
        app_template: $("#app-template").html().replace(/&lt;/g, '<').replace(/&gt;/g, '>'),

        uvdesk_shopify_create_channel_form_template: $("#uvdesk-shopify-create-channel-form-template").html().replace(/&lt;/g, '<').replace(/&gt;/g, '>'),

        uvdesk_shopify_channel_listing_template: $("#uvdesk-shopify-channel-listing-template").html().replace(/&lt;/g, '<').replace(/&gt;/g, '>'),

        uvdesk_shopify_template: $("#uvdesk-shopify-template").html().replace(/&lt;/g, '<').replace(/&gt;/g, '>'),

    };

    var ShopifyStore = Backbone.Model.extend({
        url: "./order-syncronization/api?endpoint=save-store",
        defaults: function() {
            return {
                domain: "",
                api_key: "",
                api_password: "",
                is_enabled: false,
            };
        },
        validate: function(attributes, options) {
            let validationErrors = {};

            for (let name in attributes) {
                let result = this.validateAttribute(name, attributes[name]);

                if (result !== true) {
                    validationErrors[name] = result;
                }
            }

            if (false == $.isEmptyObject(validationErrors)) {
                return validationErrors;
            }
        },
        validateAttribute: function(name, value) {
            switch (name) {
                case 'domain':
                case 'api_key':
                case 'api_password':
                    if (value == undefined || value == '') return 'This field cannot be left empty.';
                    break;
                default:
                    break;
            }

            return true;
        }
    });

    var ShopifyStoreCollection = Backbone.Collection.extend({
        url: "./order-syncronization/api?endpoint=get-stores",
        model: ShopifyStore,
        parse: function (response) {
            return response.stores;
        },
        fetch: function () {
            let collection = this;

            $.ajax({
                type: 'GET',
                url: this.url,
                dataType: 'json',
                success: function(response) {
                    collection.reset(collection.parse(response));
                },
                error: function (response) {
                }
            });
        }
    });

    var ShopifyStoreSettingsForm = Backbone.View.extend({
        el: $("#applicationDashboard"),
        template: _.template(templates.uvdesk_shopify_create_channel_form_template),
        events: {
            'input form input': 'setAttribute',
            'click .uv-btn.cancel': 'cancelForm',
            'submit form': 'submitForm'
        },
        render: function(el) {
            this.listenTo(this.model, 'sync', this.handleSync);
            this.listenTo(this.model, 'error', this.handleSyncFailure);

            el.html(this.template(this.model.toJSON()));
        },
        setAttribute: function(ev) {
            let name = $(ev.currentTarget)[0].name.trim();
            let value = $(ev.currentTarget)[0].value.trim();

            if (this.model.has(name)) {
                if (name == 'is_enabled') {
                    this.model.set(name, $(ev.currentTarget)[0].checked);
                } else {
                    this.model.set(name, value);
                }
            }
        },
        submitForm: function (ev) {
            ev.preventDefault();

            if (this.model.isValid()) {
                this.model.save({platform: 'shopify'});
            }
        },
        cancelForm: function (ev) {
            dashboard.render();
        },
        handleSync: function (model, response, options) {
            this.collection.add(model);
            app.appView.renderResponseAlert({ alertClass: 'success', alertMessage: 'Settings saved successfully' });
        },
        handleSyncFailure: function (model, xhr, options) {
            let response = xhr.responseJSON;
            let message = (typeof(response) == 'undefined' || false == response.hasOwnProperty('error')) ? 'An unexpected error occurred. Please try again later.' : response.error;

            app.appView.renderResponseAlert({ alertClass: 'danger', alertMessage: message });
        }
    });

    var ShopifyComponent = Backbone.View.extend({
        el: $("#applicationDashboard"),
        template: _.template(templates.uvdesk_shopify_template),
        channel_listing_template: _.template(templates.uvdesk_shopify_channel_listing_template),
        events: {
            'click #pta-add-shopify-store': 'addStore',
            'click .edit-channel': 'editStore',
            'click .delete-channel': 'removeStore'
        },
        initialize: function(platformDetails, shopifyStoreCollection) {
            this.platform = platformDetails;
            this.collection = shopifyStoreCollection;

            this.listenTo(shopifyStoreCollection, 'reset', this.render);
            this.listenTo(shopifyStoreCollection, 'add', this.render);
            this.listenTo(shopifyStoreCollection, 'remove', this.render);
        },
        render: function () {
            this.$el.find('.uv-ecommerce-platform.shopify').remove();
            this.$el.find('.ecommerce-platforms').append(this.template({ platform: this.platform }));

            $('#uvdesk-shopify-collection').html(this.channel_listing_template({ collection: this.collection.toJSON() }));
        },
        addStore: function (e) {
            var node = $('#uvdesk-shopify-collection');
            
            var shopifyChannel = new ShopifyStore();
            var settingsForm = new ShopifyStoreSettingsForm({ model: shopifyChannel, collection: this.collection });

            settingsForm.render(node);
        },
        editStore: function (e) {
            let id = $(e.currentTarget).closest('.uv-app-list-brick').data('id');

            var node = $('#uvdesk-shopify-collection');
            
            var shopifyChannel = this.collection.get(id);
            var settingsForm = new ShopifyStoreSettingsForm({ model: shopifyChannel, collection: this.collection });

            settingsForm.render(node);
        },
        removeStore: function (e) {
            let id = $(e.currentTarget).closest('.uv-app-list-brick').data('id');
            let shopifyChannel = this.collection.get(id);

            shopifyChannel.destroy({
                data: {
                    platform: 'shopify',
                    attributes: shopifyChannel.toJSON()
                }
            });
        }
    });

    var ECommercePlatform = Backbone.Model.extend({
        url: "./order-syncronization/api?endpoint=save-store",
        defaults: function() {
            return {
                id: "",
                title: "",
                description: "",
                channels: ""
            };
        }
    });

    var PlatformCollection = Backbone.Collection.extend({
        url: "./order-syncronization/api?endpoint=get-stores",
        model: ECommercePlatform,
        parse: function (response) {
            var collection = [];

            _.each(response.platforms, function (platformDetails, platformId) {
                collection.push({
                    id: platformId,
                    title: platformDetails.title,
                    description: platformDetails.description,
                    channels: platformDetails.channels,
                });
            })

            return collection;
        },
        fetch: function () {
            let collection = this;

            $.ajax({
                type: 'GET',
                url: this.url,
                dataType: 'json',
                success: function(response) {
                    collection.reset(collection.parse(response));
                },
                error: function (response) {
                }
            });
        }
    });

    var Dashboard = Backbone.View.extend({
        el: $("#applicationDashboard"),
        template: _.template(templates.app_template),
        initialize: function(platformCollection) {
            this.$el.empty();
            this.listenTo(platformCollection, 'reset', this.render);

            platformCollection.fetch();
        },
        render: function() {
            this.$el.empty();
            this.$el.html(this.template());

            _.each(platformCollection.toJSON(), function (platformDetails) {
                switch (platformDetails.id) {
                    case 'shopify':
                        var shopifyStoreCollection = new ShopifyStoreCollection(platformDetails.channels);
                        var shopifyView = new ShopifyComponent(platformDetails, shopifyStoreCollection);

                        shopifyView.render();
                        break;
                }
            });
        }
    });

    let platformCollection = new PlatformCollection();
    let dashboard = new Dashboard(platformCollection);
});
