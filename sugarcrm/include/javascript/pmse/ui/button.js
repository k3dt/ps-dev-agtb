/**
 * @class Button
 * Handles buttons
 * @extend Element
 *
 * @constructor
 * Create a new instance of the class
 * @param {Object} options
 * @param {Form} parent
 */
var Button = function (options, parent) {
    Element.call(this, options);
    this.parent = null;
    this.caption = null;
    this.action = null;
    this.icon = null;
    Button.prototype.initObject.call(this, options, parent);
};

Button.prototype = new Element();

Button.prototype.type = 'Button';
Button.prototype.family = 'Button';

Button.prototype.initObject = function (options, parent) {
    var defaults, self = this;
    if (options.isAction) {
        this.loadAction(options, parent);
    } else {
        defaults = {
            caption: null,
            parent: parent || null,
            jtype: 'normal',
            handler: function () {},
            icon: null
        };
        $.extend(true, defaults, options);
        this.setCaption(defaults.caption)
            .setParent(defaults.parent)
            .setIcon(defaults.icon);
        switch (defaults.jtype) {
        case 'reset':
            this.action = new Action({
                text: this.caption,
                handler: function () {
                    self.parent.reset();
                },
                cssStyle: this.icon
            });
            break;
        case 'submit':
            this.action = new Action({
                text: this.caption,
                handler: function () {
                    self.parent.submit();
                },
                cssStyle: this.icon
            });
            break;
        case 'normal':
            this.action = new Action({
                text: this.caption,
                handler: defaults.handler,
                cssStyle: this.icon
            });
            break;
        }
    }
};

Button.prototype.loadAction = function (action, parent) {
    this.action = action;
    this.setCaption(this.action.text);
    this.setIcon(this.action.cssStyle);
    this.setParent(parent);
};

Button.prototype.setCaption = function (text) {
    this.caption = text;
    return this;
};

Button.prototype.setIcon = function (value) {
    this.icon = value;
    return this;
};

Button.prototype.setParent = function (parent) {
    this.parent = parent;
    return this;
};

Button.prototype.createHTML = function () {
    var buttonAnchor, iconSpan, labelSpan;

    buttonAnchor = this.createHTMLElement('a');
    buttonAnchor.href = '#';
    buttonAnchor.className = 'adam-button';
    buttonAnchor.id = this.id;


    if (this.icon) {
        iconSpan = this.createHTMLElement('span');
        iconSpan.className = this.icon;
        buttonAnchor.appendChild(iconSpan);
    }

    labelSpan = this.createHTMLElement('span');
    labelSpan.className = 'adam-button-label';
    labelSpan.innerHTML = this.caption;
    buttonAnchor.appendChild(labelSpan);

    this.html = buttonAnchor;

    return this.html;
};

Button.prototype.attachListeners = function () {
    var self = this;
    $(this.html)
        .click(function (e) {
            e.stopPropagation();
            e.preventDefault();
            if (self.action.handler) {
                self.action.handler();
            }
        })
        .mousedown(function (e) {
            e.stopPropagation();
        });
};
