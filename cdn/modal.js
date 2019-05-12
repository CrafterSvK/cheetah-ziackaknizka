class Modal {
    constructor(template) {
        this.template = template;

        this.open();
    }

    getInput(name) {
        return this.template.querySelector(`input[name="${name}"`) || this.template.querySelector(`select[name=${name}]`);
    }

    close() {
        this.template.style.display = 'none';
    }

    open() {
        this.template.style.display = 'block';
    }
}

/**
 * Gets attribute name
 * @param value of an attribute
 * */
Object.prototype.attributeOf = function (value) {
    for (let attribute in this) if (this[attribute] === value) return attribute;

    return -1;
};
