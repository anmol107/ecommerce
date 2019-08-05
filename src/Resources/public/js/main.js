// Load the application once the DOM is ready
$(function () {

    // Initialize templates
    const templates = {
        app_template: $("#app-template").html().replace(/&lt;/g, '<').replace(/&gt;/g, '>'),

        uvdesk_shopify_create_channel_form_template: $("#uvdesk-shopify-create-channel-form-template").html().replace(/&lt;/g, '<').replace(/&gt;/g, '>'),

        uvdesk_opencart_create_channel_form_template: $("#uvdesk-opencart-create-channel-form-template").html().replace(/&lt;/g, '<').replace(/&gt;/g, '>'),

        uvdesk_bigcommerce_create_channel_form_template: $("#uvdesk-bigcommerce-create-channel-form-template").html().replace(/&lt;/g, '<').replace(/&gt;/g, '>'),

        uvdesk_magento_create_channel_form_template: $("#uvdesk-magento-create-channel-form-template").html().replace(/&lt;/g, '<').replace(/&gt;/g, '>'),

        uvdesk_shopify_channel_listing_template: $("#uvdesk-shopify-channel-listing-template").html().replace(/&lt;/g, '<').replace(/&gt;/g, '>'),

        uvdesk_opencart_channel_listing_template: $("#uvdesk-opencart-channel-listing-template").html().replace(/&lt;/g, '<').replace(/&gt;/g, '>'),

        uvdesk_bigcommerce_channel_listing_template: $("#uvdesk-bigcommerce-channel-listing-template").html().replace(/&lt;/g, '<').replace(/&gt;/g, '>'),

        uvdesk_magento_channel_listing_template: $("#uvdesk-magento-channel-listing-template").html().replace(/&lt;/g, '<').replace(/&gt;/g, '>'),
        
        uvdesk_shopify_template: $("#uvdesk-shopify-template").html().replace(/&lt;/g, '<').replace(/&gt;/g, '>'),

        uvdesk_opencart_template: $("#uvdesk-opencart-template").html().replace(/&lt;/g, '<').replace(/&gt;/g, '>'),
        
        uvdesk_bigcommerce_template: $("#uvdesk-bigcommerce-template").html().replace(/&lt;/g, '<').replace(/&gt;/g, '>'),

        uvdesk_magento_template: $("#uvdesk-magento-template").html().replace(/&lt;/g, '<').replace(/&gt;/g, '>'),
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

    var OpenCartStore = Backbone.Model.extend({
        url: "./order-syncronization/api?endpoint=save-store",
        defaults: function() {
            return {
                domain: "",
                api_key: "",
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
                default:
                    break;
            }

            return true;
        }
    });

    var BigCommerceStore = Backbone.Model.extend({
        url: "./order-syncronization/api?endpoint=save-store",
        defaults: function() {
            return {
                domain: "",
                store_hash: "",
                api_token: "",
                api_client_id: "",
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
                case 'store_hash':
                    if (value == undefined || value == '') return 'This field cannot be left empty.';
                    break;
                case 'api_token':
                    if (value == undefined || value == '') return 'This field cannot be left empty.';
                    break;
                case 'api_client_id':
                    if (value == undefined || value == '') return 'This field cannot be left empty.';
                    break;
                default:
                    break;
            }

            return true;
        }
    });

    var MagentoStore = Backbone.Model.extend({
        url: "./order-syncronization/api?endpoint=save-store",
        defaults: function() {
            return {
                domain: "",
                api_username: "",
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
                    if (value == undefined || value == '') return 'This field cannot be left empty.';
                    break;
                case 'api_username':
                    if (value == undefined || value == '') return 'This field cannot be left empty.';
                    break;
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

    var OpenCartStoreCollection = Backbone.Collection.extend({
        url: "./order-syncronization/api?endpoint=get-stores",
        model: OpenCartStore,
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

    var BigCommerceStoreCollection = Backbone.Collection.extend({
        url: "./order-syncronization/api?endpoint=get-stores",
        model: BigCommerceStore,
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

    var MagentoStoreCollection = Backbone.Collection.extend({
        url: "./order-syncronization/api?endpoint=get-stores",
        model: MagentoStore,
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
            // 'click .uv-btn.cancel': 'cancelForm',
            'click .shopify-cancel': 'cancelForm',
            // 'submit form': 'submitForm'
            'click .shopify-submit': 'submitForm'
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

    var OpenCartStoreSettingsForm = Backbone.View.extend({
        el: $("#applicationDashboard"),
        template: _.template(templates.uvdesk_opencart_create_channel_form_template),
        events: {
            'input form input': 'setAttribute',
            // 'click .uv-btn.cancel': 'cancelForm',
            'click .opencart-cancel': 'cancelForm',
            // 'submit form': 'submitForm'
            'click .opencart-submit': 'submitForm'
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
                this.model.save({platform: 'opencart'});
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

    var BigCommerceStoreSettingsForm = Backbone.View.extend({
        el: $("#applicationDashboard"),
        template: _.template(templates.uvdesk_bigcommerce_create_channel_form_template),
        events: {
            'input form input': 'setAttribute',
            // 'click .uv-btn.cancel': 'cancelForm',
            'click .bigcommerce-cancel': 'cancelForm',
            // 'submit form': 'submitForm'
            'click .bigcommerce-submit' : 'submitForm'
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
                this.model.save({platform: 'bigcommerce'});
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

    var MagentoStoreSettingsForm = Backbone.View.extend({
        el: $("#applicationDashboard"),
        template: _.template(templates.uvdesk_magento_create_channel_form_template),
        events: {
            'input form input': 'setAttribute',
            // 'click .uv-btn.cancel': 'cancelForm',
            'click .magento-cancel': 'cancelForm',
            // 'submit form': 'submitForm'
            'click .magento-submit' : 'submitForm'
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
                this.model.save({platform: 'magento'});
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

    var OpenCartComponent = Backbone.View.extend({
        el: $("#applicationDashboard"),
        template: _.template(templates.uvdesk_opencart_template),
        channel_listing_template: _.template(templates.uvdesk_opencart_channel_listing_template),
        events: {
            'click #pta-add-opencart-store': 'addStore',
            'click .edit-channel': 'editStore',
            'click .delete-channel': 'removeStore'
        },
        initialize: function(platformDetails, opencartStoreCollection) {
            this.platform = platformDetails;
            this.collection = opencartStoreCollection;

            this.listenTo(opencartStoreCollection, 'reset', this.render);
            this.listenTo(opencartStoreCollection, 'add', this.render);
            this.listenTo(opencartStoreCollection, 'remove', this.render);
        },
        render: function () {
            this.$el.find('.uv-ecommerce-platform.opencart').remove();
            this.$el.find('.ecommerce-platforms').append(this.template({ platform: this.platform }));

            $('#uvdesk-opencart-collection').html(this.channel_listing_template({ collection: this.collection.toJSON() }));
        },
        addStore: function (e) {
            var node = $('#uvdesk-opencart-collection');
            
            var opencartChannel = new OpenCartStore();
            var settingsForm = new OpenCartStoreSettingsForm({ model: opencartChannel, collection: this.collection });

            settingsForm.render(node);
        },
        editStore: function (e) {
            let id = $(e.currentTarget).closest('.uv-app-list-brick').data('id');

            var node = $('#uvdesk-opencart-collection');
            
            var opencartChannel = this.collection.get(id);
            var settingsForm = new OpenCartStoreSettingsForm({ model: opencartChannel, collection: this.collection });

            settingsForm.render(node);
        },
        removeStore: function (e) {
            let id = $(e.currentTarget).closest('.uv-app-list-brick').data('id');
            let opencartChannel = this.collection.get(id);

            opencartChannel.destroy({
                data: {
                    platform: 'opencart',
                    attributes: OpenCartChannel.toJSON()
                }
            });
        }
    });

    var BigCommerceComponent = Backbone.View.extend({
        el: $("#applicationDashboard"),
        template: _.template(templates.uvdesk_bigcommerce_template),
        channel_listing_template: _.template(templates.uvdesk_bigcommerce_channel_listing_template),
        events: {
            'click #pta-add-bigcommerce-store': 'addStore',
            'click .edit-channel': 'editStore',
            'click .delete-channel': 'removeStore'
        },
        initialize: function(platformDetails, bigcommerceStoreCollection) {
            this.platform = platformDetails;
            this.collection = bigcommerceStoreCollection;

            this.listenTo(bigcommerceStoreCollection, 'reset', this.render);
            this.listenTo(bigcommerceStoreCollection, 'add', this.render);
            this.listenTo(bigcommerceStoreCollection, 'remove', this.render);
        },
        render: function () {
            this.$el.find('.uv-ecommerce-platform.bigcommerce').remove();
            this.$el.find('.ecommerce-platforms').append(this.template({ platform: this.platform }));

            $('#uvdesk-bigcommerce-collection').html(this.channel_listing_template({ collection: this.collection.toJSON() }));
        },
        addStore: function (e) {
            var node = $('#uvdesk-bigcommerce-collection');
            
            var bigcommerceChannel = new BigCommerceStore();
            var settingsForm = new BigCommerceStoreSettingsForm({ model: bigcommerceChannel, collection: this.collection });

            settingsForm.render(node);
        },
        editStore: function (e) {
            let id = $(e.currentTarget).closest('.uv-app-list-brick').data('id');

            var node = $('#uvdesk-bigcommerce-collection');
            
            var bigcommerceChannel = this.collection.get(id);
            var settingsForm = new BigCommerceStoreSettingsForm({ model: bigcommerceChannel, collection: this.collection });

            settingsForm.render(node);
        },
        removeStore: function (e) {
            let id = $(e.currentTarget).closest('.uv-app-list-brick').data('id');
            let bigcommerceChannel = this.collection.get(id);

            bigcommerceChannel.destroy({
                data: {
                    platform: 'bigcommerce',
                    attributes: bigcommerceChannel.toJSON()
                }
            });
        }
    });

    var MagentoComponent = Backbone.View.extend({
        el: $("#applicationDashboard"),
        template: _.template(templates.uvdesk_magento_template),
        channel_listing_template: _.template(templates.uvdesk_magento_channel_listing_template),
        events: {
            'click #pta-add-magento-store': 'addStore',
            'click .edit-channel.magento': 'editStore',
            'click .delete-channel.magento': 'removeStore'
        },
        initialize: function(platformDetails, magentoStoreCollection) {
            this.platform = platformDetails;
            this.collection = magentoStoreCollection;

            this.listenTo(magentoStoreCollection, 'reset', this.render);
            this.listenTo(magentoStoreCollection, 'add', this.render);
            this.listenTo(magentoStoreCollection, 'remove', this.render);
        },
        render: function () {
            this.$el.find('.uv-ecommerce-platform.magento').remove();
            this.$el.find('.ecommerce-platforms').append(this.template({ platform: this.platform }));

            $('#uvdesk-magento-collection').html(this.channel_listing_template({ collection: this.collection.toJSON() }));
        },
        addStore: function (e) {
            var node = $('#uvdesk-magento-collection');
            
            var magentoChannel = new MagentoStore();
            var settingsForm = new MagentoStoreSettingsForm({ model: magentoChannel, collection: this.collection });

            settingsForm.render(node);
        },
        editStore: function (e) {
            let id = $(e.currentTarget).closest('.uv-app-list-brick').data('id');

            var node = $('#uvdesk-magento-collection');
            
            var magentoChannel = this.collection.get(id);
            var settingsForm = new magentoStoreSettingsForm({ model: magentoChannel, collection: this.collection });

            settingsForm.render(node);
        },
        removeStore: function (e) {
            let id = $(e.currentTarget).closest('.uv-app-list-brick').data('id');
            let magentoChannel = this.collection.get(id);

            magentoChannel.destroy({
                data: {
                    platform: 'magento',
                    attributes: magentoChannel.toJSON()
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

                    case 'bigcommerce':
                        var bigcommerceStoreCollection = new BigCommerceStoreCollection(platformDetails.channels);
                        var bigcommerceView = new BigCommerceComponent(platformDetails, bigcommerceStoreCollection);
                        bigcommerceView.render();
                        break;

                    case 'magento':
                        var magentoStoreCollection = new MagentoStoreCollection(platformDetails.channels);
                        var magentoView = new MagentoComponent(platformDetails, magentoStoreCollection);
                        magentoView.render();
                        break; 

                    case 'opencart':
                        var opencartStoreCollection = new OpenCartStoreCollection(platformDetails.channels);
                        var opencartView = new OpenCartComponent(platformDetails, opencartStoreCollection);
                        opencartView.render();
                        break;                         
                }
            });
        }
    });

    let platformCollection = new PlatformCollection();
    let dashboard = new Dashboard(platformCollection);
});
