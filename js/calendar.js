/**
 * Calendar
 * Javascript Calendar Component
 *
 * @filesource js/calendar.js
 * @link http://www.kotchasan.com/
 * @copyright 2018 Goragod.com
 * @license http://www.kotchasan.com/license/
 */
window.Calendar = GClass.create();
Calendar.prototype = {
  initialize: function(id, o) {
    this.id = id;
    this.url = null;
    this.params = "";
    this.onclick = $K.emptyFunction;
    this.events = {};
    this.cdate = new Date();
    this.calendar = $G(document.createElement("div"));
    this.calendar.className = "event-calendar";
    this.showToday = false;
    this.first_day_of_calendar = null;
    this.next_day_of_calendar = null;
    for (var property in o) {
      if (property == "month") {
        this.cdate.setMonth(floatval(o[property]) - 1);
      } else if (property == "year") {
        this.cdate.setFullYear(floatval(o[property]));
      } else if (property == "class") {
        this.calendar.className = o[property];
      } else {
        this[property] = o[property];
      }
    }
    $E(id).appendChild(this.calendar);
    self = this;
    $G(window).addEvent("resize", function() {
      self._resize();
    });
    this.setDate(this.cdate);
  },
  _resize: function() {
    var cw = this.calendar.getClientWidth(),
      w = cw / 7;
    document.css("#" + this.id + " td div{width:" + (w - 2) + "px}#" + this.id + " td{width:" + w + "px;height:" + w + "px}", this.id);
  },
  _drawMonth: function() {
    var self = this,
      header = document.createElement("div");
    header.className = "header";
    this.calendar.innerHTML = "";
    this.calendar.appendChild(header);
    this.first_day_of_calendar = null;
    this.next_day_of_calendar = null;
    var a = document.createElement("a"),
      span = document.createElement("span");
    a.className = "prev";
    a.title = trans("Prev Month");
    header.appendChild(a);
    span.innerHTML = a.title;
    a.appendChild(span);
    callClick(a, function() {
      self._move(-1);
    });
    a = document.createElement("a");
    a.className = "curr";
    header.appendChild(a);
    a.innerHTML = this.cdate.format("F Y");
    a = document.createElement("a");
    span = document.createElement("span");
    a.className = "next";
    a.title = trans("Next Month");
    header.appendChild(a);
    span.innerHTML = a.title;
    a.appendChild(span);
    callClick(a, function() {
      self._move(1);
    });
    var table = document.createElement("table"),
      thead = document.createElement("thead"),
      tbody = document.createElement("tbody");
    this.calendar.appendChild(table);
    table.appendChild(thead);
    table.appendChild(tbody);
    var intmonth = this.cdate.getMonth() + 1,
      intyear = this.cdate.getFullYear(),
      cls = "",
      today = new Date(),
      today_month = today.getMonth() + 1,
      today_year = today.getFullYear(),
      today_date = today.getDate(),
      r = 0,
      c = 0,
      row,
      cell;
    row = thead.insertRow(0);
    forEach(Date.dayNames, function(item, i) {
      cell = document.createElement("th");
      row.appendChild(cell);
      cell.appendChild(document.createTextNode(item));
    });
    var tmp_prev_month = intmonth - 1,
      tmp_next_month = intmonth + 1,
      tmp_next_year = intyear,
      tmp_prev_year = intyear;
    if (tmp_prev_month == 0) {
      tmp_prev_month = 12;
      tmp_prev_year--;
    }
    if (tmp_next_month == 13) {
      tmp_next_month = 1;
      tmp_next_year++;
    }
    var initial_day = 1,
      tmp_init = new Date(intyear, intmonth, 1, 0, 0, 0, 0).dayOfWeek(),
      max_prev = new Date(tmp_prev_year, tmp_prev_month, 0, 0, 0, 0, 0).daysInMonth(),
      max_this = new Date(intyear, intmonth, 0, 0, 0, 0, 0).daysInMonth();
    if (tmp_init !== 0) {
      initial_day = max_prev - (tmp_init - 1);
    }
    tmp_next_year = tmp_next_year.toString();
    tmp_prev_year = tmp_prev_year.toString();
    tmp_next_month = tmp_next_month.toString();
    tmp_prev_month = tmp_prev_month.toString();
    var pointer = initial_day,
      flag_init = initial_day == 1 ? 1 : 0,
      tmp_month = initial_day == 1 ? intmonth : floatval(tmp_prev_month),
      tmp_year = initial_day == 1 ? intyear : floatval(tmp_prev_year),
      flag_end = 0,
      d,
      div;
    r = 0;
    for (var x = 0; x < 42; x++) {
      if (tmp_init !== 0 && pointer > max_prev && flag_init == 0) {
        flag_init = 1;
        pointer = 1;
        tmp_month = intmonth;
        tmp_year = intyear;
      }
      if (flag_init == 1 && flag_end == 0 && pointer > max_this) {
        flag_end = 1;
        pointer = 1;
        tmp_month = floatval(tmp_next_month);
        tmp_year = floatval(tmp_next_year);
      }
      c = x % 7;
      if (c == 0) {
        row = tbody.insertRow(r);
        r++;
      }
      cell = row.insertCell(c);
      span = document.createElement("span");
      span.innerHTML = pointer;
      cell.appendChild(span);
      div = document.createElement("div");
      d = new Date(tmp_year, tmp_month - 1, pointer, 0, 0, 0, 0);
      if (self.first_day_of_calendar === null) {
        self.first_day_of_calendar = d;
      }
      div.id = this.id + "-" + d.format("y-m-d");
      cell.appendChild(div);
      cls = tmp_month == intmonth ? "curr" : "ex";
      if (tmp_year == today_year && tmp_month == today_month && pointer == today_date) {
        cls += " today";
      }
      cell.className = cls;
      pointer++;
    }
    this.next_day_of_calendar = new Date(tmp_year, tmp_month - 1, pointer, 0, 0, 0, 0);
    if (this.showToday) {
      var a = document.createElement("a");
      a.innerHTML = new Date().format("d F Y");
      a.className = "set-today";
      this.calendar.appendChild(a);
      a.onclick = function() {
        self.setDate(new Date());
      };
    }
    this._resize();
  },
  _addLabel: function(d, prop, first) {
    var self = this,
      div = $E(this.id + "-" + d.format("y-m-d"));
    if (div) {
      var a = document.createElement("a");
      if (prop.id) {
        a.id = prop.id;
      }
      if (prop.title) {
        a.title = prop.title;
        if (first) {
          a.innerHTML = "<span>" + prop.title + "</span>";
        } else {
          a.innerHTML = "<span>&nbsp;</span>";
        }
      } else {
        a.innerHTML = "<span>&nbsp;</span>";
      }
      if (prop.url) {
        a.href = prop.url;
      }
      if (prop.color) {
        a.style.backgroundColor = prop.color;
      }
      if (!first) {
        a.className = "sub";
      }
      div.appendChild(a);
      a.onclick = function() {
        return self.onclick.call(this, d);
      };
      return a;
    }
    return null;
  },
  _drawEvents: function() {
    var a,
      diff,
      diff_start,
      elems,
      top,
      start,
      start_date,
      d,
      self = this;
    forEach(this.events, function() {
      if (this.start) {
        start_date = this.start.split('T')[0].replace(/-/g, '/');
        a = new Date(start_date);
        diff_next = a.compare(self.next_day_of_calendar);
        if (diff_next.days >= -42 || this.end) {
          diff_start = a.compare(self.first_day_of_calendar);
          if (diff_next.days >= -42) {
            if (diff_start.days < 0) {
              a = self._addLabel(self.first_day_of_calendar, this, true);
              start_date = self.first_day_of_calendar.format("y/m/d H:i:s");
            } else {
              a = self._addLabel(a, this, true);
            }
          }
          if (a && this.end) {
            diff = new Date(this.end.split('T')[0].replace(/-/g, '/')).compare(new Date(start_date));
            if (diff_start.days < 0) {
              diff.days--;
            }
            if (diff.days > 0) {
              elems = [a];
              top = a.offsetTop;
              start = Date.parse(start_date);
              for (var i = 1; i <= diff.days; i++) {
                d = new Date(start + i * 86400000);
                a = self._addLabel(d, this, false);
                if (a) {
                  if (d.getDay() == 0) {
                    self._align(elems, top);
                    top = a.offsetTop;
                    elems = [a];
                  } else {
                    top = Math.max(top, a.offsetTop);
                    elems.push(a);
                  }
                }
              }
              self._align(elems, top);
            }
          }
        }
      }
    });
  },
  _align: function(elems, top) {
    forEach(elems, function() {
      if (this.offsetTop != top) {
        this.style.top = top + "px";
      }
    });
  },
  _get: function(d) {
    var self = this,
      q = ["month=" + (floatval(d.getMonth()) + 1), "year=" + d.getFullYear()];
    q = (this.params == "" ? "" : this.params + "&") + q.join("&");
    new GAjax().send(this.url, q, function(xhr) {
      var ds = xhr.responseText.toJSON();
      self.cdate = d;
      self.events = ds || {};
      self._drawMonth();
      self._drawEvents();
    });
  },
  _move: function(value) {
    var d = new Date();
    d.setTime(this.cdate.valueOf());
    d.setMonth(d.getMonth() + value);
    this.setDate(d);
  },
  setEvents: function(events) {
    this.events = events;
    this._drawEvents();
  },
  setDate: function(date) {
    if (this.url !== null) {
      this._get(date);
    } else {
      this.cdate = date;
      this._drawMonth();
      this._drawEvents();
    }
  }
};
