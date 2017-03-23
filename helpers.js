Array.prototype.swap = function(a, b) {
    var t = this[a];
    this[a] = this[b];
    this[b] = t;
    return this;
};
