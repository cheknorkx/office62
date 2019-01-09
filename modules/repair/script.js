function initRepairGet() {
  var o = {
    get: function () {
      return "id=" + $E("id").value + "&" + this.name + "=" + this.value;
    },
    onSuccess: function () {
      equipment.valid();
      serial.valid();
    },
    onChanged: function () {
      $E("inventory_id").value = 0;
      equipment.reset();
      serial.reset();
    }
  };
  var equipment = initAutoComplete(
    "equipment",
    WEB_URL + "index.php/inventory/model/autocomplete/find",
    "equipment,serial",
    "find",
    o
    );
  var serial = initAutoComplete(
    "serial",
    WEB_URL + "index.php/inventory/model/autocomplete/find",
    "serial,equipment",
    "find",
    o
    );
}
function initRepairDownload(id) {
  var doDelete = function () {
    if (confirm(trans("You want to XXX ?").replace(/XXX/, trans("delete")))) {
      send(
        "index.php/repair/model/detail/action",
        "id=" + this.id,
        doFormSubmit,
        this
        );
    }
  };
  forEach($G(id).elems("a"), function () {
    if (/^delete_([a-z0-9]+)$/.test(this.id)) {
      callClick(this, doDelete);
    }
  });
}
