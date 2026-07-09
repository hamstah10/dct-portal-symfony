var Ic = Object.defineProperty;
var Uc = (e, t, n) => t in e ? Ic(e, t, { enumerable: !0, configurable: !0, writable: !0, value: n }) : e[t] = n;
var Vl = (e, t, n) => Uc(e, typeof t != "symbol" ? t + "" : t, n);
var ws = { exports: {} }, Cl = {}, Ss = { exports: {} }, R = {};
/**
 * @license React
 * react.production.min.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */
var wr = Symbol.for("react.element"), $c = Symbol.for("react.portal"), Bc = Symbol.for("react.fragment"), Vc = Symbol.for("react.strict_mode"), Hc = Symbol.for("react.profiler"), Qc = Symbol.for("react.provider"), Kc = Symbol.for("react.context"), Gc = Symbol.for("react.forward_ref"), Yc = Symbol.for("react.suspense"), Xc = Symbol.for("react.memo"), Zc = Symbol.for("react.lazy"), uu = Symbol.iterator;
function Jc(e) {
  return e === null || typeof e != "object" ? null : (e = uu && e[uu] || e["@@iterator"], typeof e == "function" ? e : null);
}
var xs = { isMounted: function() {
  return !1;
}, enqueueForceUpdate: function() {
}, enqueueReplaceState: function() {
}, enqueueSetState: function() {
} }, _s = Object.assign, Es = {};
function Pn(e, t, n) {
  this.props = e, this.context = t, this.refs = Es, this.updater = n || xs;
}
Pn.prototype.isReactComponent = {};
Pn.prototype.setState = function(e, t) {
  if (typeof e != "object" && typeof e != "function" && e != null) throw Error("setState(...): takes an object of state variables to update or a function which returns an object of state variables.");
  this.updater.enqueueSetState(this, e, t, "setState");
};
Pn.prototype.forceUpdate = function(e) {
  this.updater.enqueueForceUpdate(this, e, "forceUpdate");
};
function zs() {
}
zs.prototype = Pn.prototype;
function ao(e, t, n) {
  this.props = e, this.context = t, this.refs = Es, this.updater = n || xs;
}
var co = ao.prototype = new zs();
co.constructor = ao;
_s(co, Pn.prototype);
co.isPureReactComponent = !0;
var su = Array.isArray, Cs = Object.prototype.hasOwnProperty, fo = { current: null }, Ns = { key: !0, ref: !0, __self: !0, __source: !0 };
function js(e, t, n) {
  var r, l = {}, i = null, o = null;
  if (t != null) for (r in t.ref !== void 0 && (o = t.ref), t.key !== void 0 && (i = "" + t.key), t) Cs.call(t, r) && !Ns.hasOwnProperty(r) && (l[r] = t[r]);
  var u = arguments.length - 2;
  if (u === 1) l.children = n;
  else if (1 < u) {
    for (var s = Array(u), c = 0; c < u; c++) s[c] = arguments[c + 2];
    l.children = s;
  }
  if (e && e.defaultProps) for (r in u = e.defaultProps, u) l[r] === void 0 && (l[r] = u[r]);
  return { $$typeof: wr, type: e, key: i, ref: o, props: l, _owner: fo.current };
}
function qc(e, t) {
  return { $$typeof: wr, type: e.type, key: t, ref: e.ref, props: e.props, _owner: e._owner };
}
function po(e) {
  return typeof e == "object" && e !== null && e.$$typeof === wr;
}
function bc(e) {
  var t = { "=": "=0", ":": "=2" };
  return "$" + e.replace(/[=:]/g, function(n) {
    return t[n];
  });
}
var au = /\/+/g;
function Hl(e, t) {
  return typeof e == "object" && e !== null && e.key != null ? bc("" + e.key) : t.toString(36);
}
function Hr(e, t, n, r, l) {
  var i = typeof e;
  (i === "undefined" || i === "boolean") && (e = null);
  var o = !1;
  if (e === null) o = !0;
  else switch (i) {
    case "string":
    case "number":
      o = !0;
      break;
    case "object":
      switch (e.$$typeof) {
        case wr:
        case $c:
          o = !0;
      }
  }
  if (o) return o = e, l = l(o), e = r === "" ? "." + Hl(o, 0) : r, su(l) ? (n = "", e != null && (n = e.replace(au, "$&/") + "/"), Hr(l, t, n, "", function(c) {
    return c;
  })) : l != null && (po(l) && (l = qc(l, n + (!l.key || o && o.key === l.key ? "" : ("" + l.key).replace(au, "$&/") + "/") + e)), t.push(l)), 1;
  if (o = 0, r = r === "" ? "." : r + ":", su(e)) for (var u = 0; u < e.length; u++) {
    i = e[u];
    var s = r + Hl(i, u);
    o += Hr(i, t, n, s, l);
  }
  else if (s = Jc(e), typeof s == "function") for (e = s.call(e), u = 0; !(i = e.next()).done; ) i = i.value, s = r + Hl(i, u++), o += Hr(i, t, n, s, l);
  else if (i === "object") throw t = String(e), Error("Objects are not valid as a React child (found: " + (t === "[object Object]" ? "object with keys {" + Object.keys(e).join(", ") + "}" : t) + "). If you meant to render a collection of children, use an array instead.");
  return o;
}
function Cr(e, t, n) {
  if (e == null) return e;
  var r = [], l = 0;
  return Hr(e, r, "", "", function(i) {
    return t.call(n, i, l++);
  }), r;
}
function ed(e) {
  if (e._status === -1) {
    var t = e._result;
    t = t(), t.then(function(n) {
      (e._status === 0 || e._status === -1) && (e._status = 1, e._result = n);
    }, function(n) {
      (e._status === 0 || e._status === -1) && (e._status = 2, e._result = n);
    }), e._status === -1 && (e._status = 0, e._result = t);
  }
  if (e._status === 1) return e._result.default;
  throw e._result;
}
var ke = { current: null }, Qr = { transition: null }, td = { ReactCurrentDispatcher: ke, ReactCurrentBatchConfig: Qr, ReactCurrentOwner: fo };
function Ps() {
  throw Error("act(...) is not supported in production builds of React.");
}
R.Children = { map: Cr, forEach: function(e, t, n) {
  Cr(e, function() {
    t.apply(this, arguments);
  }, n);
}, count: function(e) {
  var t = 0;
  return Cr(e, function() {
    t++;
  }), t;
}, toArray: function(e) {
  return Cr(e, function(t) {
    return t;
  }) || [];
}, only: function(e) {
  if (!po(e)) throw Error("React.Children.only expected to receive a single React element child.");
  return e;
} };
R.Component = Pn;
R.Fragment = Bc;
R.Profiler = Hc;
R.PureComponent = ao;
R.StrictMode = Vc;
R.Suspense = Yc;
R.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED = td;
R.act = Ps;
R.cloneElement = function(e, t, n) {
  if (e == null) throw Error("React.cloneElement(...): The argument must be a React element, but you passed " + e + ".");
  var r = _s({}, e.props), l = e.key, i = e.ref, o = e._owner;
  if (t != null) {
    if (t.ref !== void 0 && (i = t.ref, o = fo.current), t.key !== void 0 && (l = "" + t.key), e.type && e.type.defaultProps) var u = e.type.defaultProps;
    for (s in t) Cs.call(t, s) && !Ns.hasOwnProperty(s) && (r[s] = t[s] === void 0 && u !== void 0 ? u[s] : t[s]);
  }
  var s = arguments.length - 2;
  if (s === 1) r.children = n;
  else if (1 < s) {
    u = Array(s);
    for (var c = 0; c < s; c++) u[c] = arguments[c + 2];
    r.children = u;
  }
  return { $$typeof: wr, type: e.type, key: l, ref: i, props: r, _owner: o };
};
R.createContext = function(e) {
  return e = { $$typeof: Kc, _currentValue: e, _currentValue2: e, _threadCount: 0, Provider: null, Consumer: null, _defaultValue: null, _globalName: null }, e.Provider = { $$typeof: Qc, _context: e }, e.Consumer = e;
};
R.createElement = js;
R.createFactory = function(e) {
  var t = js.bind(null, e);
  return t.type = e, t;
};
R.createRef = function() {
  return { current: null };
};
R.forwardRef = function(e) {
  return { $$typeof: Gc, render: e };
};
R.isValidElement = po;
R.lazy = function(e) {
  return { $$typeof: Zc, _payload: { _status: -1, _result: e }, _init: ed };
};
R.memo = function(e, t) {
  return { $$typeof: Xc, type: e, compare: t === void 0 ? null : t };
};
R.startTransition = function(e) {
  var t = Qr.transition;
  Qr.transition = {};
  try {
    e();
  } finally {
    Qr.transition = t;
  }
};
R.unstable_act = Ps;
R.useCallback = function(e, t) {
  return ke.current.useCallback(e, t);
};
R.useContext = function(e) {
  return ke.current.useContext(e);
};
R.useDebugValue = function() {
};
R.useDeferredValue = function(e) {
  return ke.current.useDeferredValue(e);
};
R.useEffect = function(e, t) {
  return ke.current.useEffect(e, t);
};
R.useId = function() {
  return ke.current.useId();
};
R.useImperativeHandle = function(e, t, n) {
  return ke.current.useImperativeHandle(e, t, n);
};
R.useInsertionEffect = function(e, t) {
  return ke.current.useInsertionEffect(e, t);
};
R.useLayoutEffect = function(e, t) {
  return ke.current.useLayoutEffect(e, t);
};
R.useMemo = function(e, t) {
  return ke.current.useMemo(e, t);
};
R.useReducer = function(e, t, n) {
  return ke.current.useReducer(e, t, n);
};
R.useRef = function(e) {
  return ke.current.useRef(e);
};
R.useState = function(e) {
  return ke.current.useState(e);
};
R.useSyncExternalStore = function(e, t, n) {
  return ke.current.useSyncExternalStore(e, t, n);
};
R.useTransition = function() {
  return ke.current.useTransition();
};
R.version = "18.3.1";
Ss.exports = R;
var X = Ss.exports;
/**
 * @license React
 * react-jsx-runtime.production.min.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */
var nd = X, rd = Symbol.for("react.element"), ld = Symbol.for("react.fragment"), id = Object.prototype.hasOwnProperty, od = nd.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED.ReactCurrentOwner, ud = { key: !0, ref: !0, __self: !0, __source: !0 };
function Ts(e, t, n) {
  var r, l = {}, i = null, o = null;
  n !== void 0 && (i = "" + n), t.key !== void 0 && (i = "" + t.key), t.ref !== void 0 && (o = t.ref);
  for (r in t) id.call(t, r) && !ud.hasOwnProperty(r) && (l[r] = t[r]);
  if (e && e.defaultProps) for (r in t = e.defaultProps, t) l[r] === void 0 && (l[r] = t[r]);
  return { $$typeof: rd, type: e, key: i, ref: o, props: l, _owner: od.current };
}
Cl.Fragment = ld;
Cl.jsx = Ts;
Cl.jsxs = Ts;
ws.exports = Cl;
var d = ws.exports, Ls = { exports: {} }, Te = {}, Fs = { exports: {} }, Ms = {};
/**
 * @license React
 * scheduler.production.min.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */
(function(e) {
  function t(z, T) {
    var F = z.length;
    z.push(T);
    e: for (; 0 < F; ) {
      var Y = F - 1 >>> 1, V = z[Y];
      if (0 < l(V, T)) z[Y] = T, z[F] = V, F = Y;
      else break e;
    }
  }
  function n(z) {
    return z.length === 0 ? null : z[0];
  }
  function r(z) {
    if (z.length === 0) return null;
    var T = z[0], F = z.pop();
    if (F !== T) {
      z[0] = F;
      e: for (var Y = 0, V = z.length, qt = V >>> 1; Y < qt; ) {
        var be = 2 * (Y + 1) - 1, et = z[be], tt = be + 1, Dt = z[tt];
        if (0 > l(et, F)) tt < V && 0 > l(Dt, et) ? (z[Y] = Dt, z[tt] = F, Y = tt) : (z[Y] = et, z[be] = F, Y = be);
        else if (tt < V && 0 > l(Dt, F)) z[Y] = Dt, z[tt] = F, Y = tt;
        else break e;
      }
    }
    return T;
  }
  function l(z, T) {
    var F = z.sortIndex - T.sortIndex;
    return F !== 0 ? F : z.id - T.id;
  }
  if (typeof performance == "object" && typeof performance.now == "function") {
    var i = performance;
    e.unstable_now = function() {
      return i.now();
    };
  } else {
    var o = Date, u = o.now();
    e.unstable_now = function() {
      return o.now() - u;
    };
  }
  var s = [], c = [], m = 1, h = null, g = 3, k = !1, x = !1, w = !1, U = typeof setTimeout == "function" ? setTimeout : null, f = typeof clearTimeout == "function" ? clearTimeout : null, a = typeof setImmediate < "u" ? setImmediate : null;
  typeof navigator < "u" && navigator.scheduling !== void 0 && navigator.scheduling.isInputPending !== void 0 && navigator.scheduling.isInputPending.bind(navigator.scheduling);
  function p(z) {
    for (var T = n(c); T !== null; ) {
      if (T.callback === null) r(c);
      else if (T.startTime <= z) r(c), T.sortIndex = T.expirationTime, t(s, T);
      else break;
      T = n(c);
    }
  }
  function v(z) {
    if (w = !1, p(z), !x) if (n(s) !== null) x = !0, Mn(E);
    else {
      var T = n(c);
      T !== null && mt(v, T.startTime - z);
    }
  }
  function E(z, T) {
    x = !1, w && (w = !1, f(j), j = -1), k = !0;
    var F = g;
    try {
      for (p(T), h = n(s); h !== null && (!(h.expirationTime > T) || z && !te()); ) {
        var Y = h.callback;
        if (typeof Y == "function") {
          h.callback = null, g = h.priorityLevel;
          var V = Y(h.expirationTime <= T);
          T = e.unstable_now(), typeof V == "function" ? h.callback = V : h === n(s) && r(s), p(T);
        } else r(s);
        h = n(s);
      }
      if (h !== null) var qt = !0;
      else {
        var be = n(c);
        be !== null && mt(v, be.startTime - T), qt = !1;
      }
      return qt;
    } finally {
      h = null, g = F, k = !1;
    }
  }
  var _ = !1, N = null, j = -1, W = 5, M = -1;
  function te() {
    return !(e.unstable_now() - M < W);
  }
  function ie() {
    if (N !== null) {
      var z = e.unstable_now();
      M = z;
      var T = !0;
      try {
        T = N(!0, z);
      } finally {
        T ? pt() : (_ = !1, N = null);
      }
    } else _ = !1;
  }
  var pt;
  if (typeof a == "function") pt = function() {
    a(ie);
  };
  else if (typeof MessageChannel < "u") {
    var Fn = new MessageChannel(), zr = Fn.port2;
    Fn.port1.onmessage = ie, pt = function() {
      zr.postMessage(null);
    };
  } else pt = function() {
    U(ie, 0);
  };
  function Mn(z) {
    N = z, _ || (_ = !0, pt());
  }
  function mt(z, T) {
    j = U(function() {
      z(e.unstable_now());
    }, T);
  }
  e.unstable_IdlePriority = 5, e.unstable_ImmediatePriority = 1, e.unstable_LowPriority = 4, e.unstable_NormalPriority = 3, e.unstable_Profiling = null, e.unstable_UserBlockingPriority = 2, e.unstable_cancelCallback = function(z) {
    z.callback = null;
  }, e.unstable_continueExecution = function() {
    x || k || (x = !0, Mn(E));
  }, e.unstable_forceFrameRate = function(z) {
    0 > z || 125 < z ? console.error("forceFrameRate takes a positive int between 0 and 125, forcing frame rates higher than 125 fps is not supported") : W = 0 < z ? Math.floor(1e3 / z) : 5;
  }, e.unstable_getCurrentPriorityLevel = function() {
    return g;
  }, e.unstable_getFirstCallbackNode = function() {
    return n(s);
  }, e.unstable_next = function(z) {
    switch (g) {
      case 1:
      case 2:
      case 3:
        var T = 3;
        break;
      default:
        T = g;
    }
    var F = g;
    g = T;
    try {
      return z();
    } finally {
      g = F;
    }
  }, e.unstable_pauseExecution = function() {
  }, e.unstable_requestPaint = function() {
  }, e.unstable_runWithPriority = function(z, T) {
    switch (z) {
      case 1:
      case 2:
      case 3:
      case 4:
      case 5:
        break;
      default:
        z = 3;
    }
    var F = g;
    g = z;
    try {
      return T();
    } finally {
      g = F;
    }
  }, e.unstable_scheduleCallback = function(z, T, F) {
    var Y = e.unstable_now();
    switch (typeof F == "object" && F !== null ? (F = F.delay, F = typeof F == "number" && 0 < F ? Y + F : Y) : F = Y, z) {
      case 1:
        var V = -1;
        break;
      case 2:
        V = 250;
        break;
      case 5:
        V = 1073741823;
        break;
      case 4:
        V = 1e4;
        break;
      default:
        V = 5e3;
    }
    return V = F + V, z = { id: m++, callback: T, priorityLevel: z, startTime: F, expirationTime: V, sortIndex: -1 }, F > Y ? (z.sortIndex = F, t(c, z), n(s) === null && z === n(c) && (w ? (f(j), j = -1) : w = !0, mt(v, F - Y))) : (z.sortIndex = V, t(s, z), x || k || (x = !0, Mn(E))), z;
  }, e.unstable_shouldYield = te, e.unstable_wrapCallback = function(z) {
    var T = g;
    return function() {
      var F = g;
      g = T;
      try {
        return z.apply(this, arguments);
      } finally {
        g = F;
      }
    };
  };
})(Ms);
Fs.exports = Ms;
var sd = Fs.exports;
/**
 * @license React
 * react-dom.production.min.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */
var ad = X, Pe = sd;
function y(e) {
  for (var t = "https://reactjs.org/docs/error-decoder.html?invariant=" + e, n = 1; n < arguments.length; n++) t += "&args[]=" + encodeURIComponent(arguments[n]);
  return "Minified React error #" + e + "; visit " + t + " for the full message or use the non-minified dev environment for full errors and additional helpful warnings.";
}
var As = /* @__PURE__ */ new Set(), rr = {};
function Zt(e, t) {
  Sn(e, t), Sn(e + "Capture", t);
}
function Sn(e, t) {
  for (rr[e] = t, e = 0; e < t.length; e++) As.add(t[e]);
}
var st = !(typeof window > "u" || typeof window.document > "u" || typeof window.document.createElement > "u"), vi = Object.prototype.hasOwnProperty, cd = /^[:A-Z_a-z\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u02FF\u0370-\u037D\u037F-\u1FFF\u200C-\u200D\u2070-\u218F\u2C00-\u2FEF\u3001-\uD7FF\uF900-\uFDCF\uFDF0-\uFFFD][:A-Z_a-z\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u02FF\u0370-\u037D\u037F-\u1FFF\u200C-\u200D\u2070-\u218F\u2C00-\u2FEF\u3001-\uD7FF\uF900-\uFDCF\uFDF0-\uFFFD\-.0-9\u00B7\u0300-\u036F\u203F-\u2040]*$/, cu = {}, du = {};
function dd(e) {
  return vi.call(du, e) ? !0 : vi.call(cu, e) ? !1 : cd.test(e) ? du[e] = !0 : (cu[e] = !0, !1);
}
function fd(e, t, n, r) {
  if (n !== null && n.type === 0) return !1;
  switch (typeof t) {
    case "function":
    case "symbol":
      return !0;
    case "boolean":
      return r ? !1 : n !== null ? !n.acceptsBooleans : (e = e.toLowerCase().slice(0, 5), e !== "data-" && e !== "aria-");
    default:
      return !1;
  }
}
function pd(e, t, n, r) {
  if (t === null || typeof t > "u" || fd(e, t, n, r)) return !0;
  if (r) return !1;
  if (n !== null) switch (n.type) {
    case 3:
      return !t;
    case 4:
      return t === !1;
    case 5:
      return isNaN(t);
    case 6:
      return isNaN(t) || 1 > t;
  }
  return !1;
}
function we(e, t, n, r, l, i, o) {
  this.acceptsBooleans = t === 2 || t === 3 || t === 4, this.attributeName = r, this.attributeNamespace = l, this.mustUseProperty = n, this.propertyName = e, this.type = t, this.sanitizeURL = i, this.removeEmptyString = o;
}
var ce = {};
"children dangerouslySetInnerHTML defaultValue defaultChecked innerHTML suppressContentEditableWarning suppressHydrationWarning style".split(" ").forEach(function(e) {
  ce[e] = new we(e, 0, !1, e, null, !1, !1);
});
[["acceptCharset", "accept-charset"], ["className", "class"], ["htmlFor", "for"], ["httpEquiv", "http-equiv"]].forEach(function(e) {
  var t = e[0];
  ce[t] = new we(t, 1, !1, e[1], null, !1, !1);
});
["contentEditable", "draggable", "spellCheck", "value"].forEach(function(e) {
  ce[e] = new we(e, 2, !1, e.toLowerCase(), null, !1, !1);
});
["autoReverse", "externalResourcesRequired", "focusable", "preserveAlpha"].forEach(function(e) {
  ce[e] = new we(e, 2, !1, e, null, !1, !1);
});
"allowFullScreen async autoFocus autoPlay controls default defer disabled disablePictureInPicture disableRemotePlayback formNoValidate hidden loop noModule noValidate open playsInline readOnly required reversed scoped seamless itemScope".split(" ").forEach(function(e) {
  ce[e] = new we(e, 3, !1, e.toLowerCase(), null, !1, !1);
});
["checked", "multiple", "muted", "selected"].forEach(function(e) {
  ce[e] = new we(e, 3, !0, e, null, !1, !1);
});
["capture", "download"].forEach(function(e) {
  ce[e] = new we(e, 4, !1, e, null, !1, !1);
});
["cols", "rows", "size", "span"].forEach(function(e) {
  ce[e] = new we(e, 6, !1, e, null, !1, !1);
});
["rowSpan", "start"].forEach(function(e) {
  ce[e] = new we(e, 5, !1, e.toLowerCase(), null, !1, !1);
});
var mo = /[\-:]([a-z])/g;
function ho(e) {
  return e[1].toUpperCase();
}
"accent-height alignment-baseline arabic-form baseline-shift cap-height clip-path clip-rule color-interpolation color-interpolation-filters color-profile color-rendering dominant-baseline enable-background fill-opacity fill-rule flood-color flood-opacity font-family font-size font-size-adjust font-stretch font-style font-variant font-weight glyph-name glyph-orientation-horizontal glyph-orientation-vertical horiz-adv-x horiz-origin-x image-rendering letter-spacing lighting-color marker-end marker-mid marker-start overline-position overline-thickness paint-order panose-1 pointer-events rendering-intent shape-rendering stop-color stop-opacity strikethrough-position strikethrough-thickness stroke-dasharray stroke-dashoffset stroke-linecap stroke-linejoin stroke-miterlimit stroke-opacity stroke-width text-anchor text-decoration text-rendering underline-position underline-thickness unicode-bidi unicode-range units-per-em v-alphabetic v-hanging v-ideographic v-mathematical vector-effect vert-adv-y vert-origin-x vert-origin-y word-spacing writing-mode xmlns:xlink x-height".split(" ").forEach(function(e) {
  var t = e.replace(
    mo,
    ho
  );
  ce[t] = new we(t, 1, !1, e, null, !1, !1);
});
"xlink:actuate xlink:arcrole xlink:role xlink:show xlink:title xlink:type".split(" ").forEach(function(e) {
  var t = e.replace(mo, ho);
  ce[t] = new we(t, 1, !1, e, "http://www.w3.org/1999/xlink", !1, !1);
});
["xml:base", "xml:lang", "xml:space"].forEach(function(e) {
  var t = e.replace(mo, ho);
  ce[t] = new we(t, 1, !1, e, "http://www.w3.org/XML/1998/namespace", !1, !1);
});
["tabIndex", "crossOrigin"].forEach(function(e) {
  ce[e] = new we(e, 1, !1, e.toLowerCase(), null, !1, !1);
});
ce.xlinkHref = new we("xlinkHref", 1, !1, "xlink:href", "http://www.w3.org/1999/xlink", !0, !1);
["src", "href", "action", "formAction"].forEach(function(e) {
  ce[e] = new we(e, 1, !1, e.toLowerCase(), null, !0, !0);
});
function go(e, t, n, r) {
  var l = ce.hasOwnProperty(t) ? ce[t] : null;
  (l !== null ? l.type !== 0 : r || !(2 < t.length) || t[0] !== "o" && t[0] !== "O" || t[1] !== "n" && t[1] !== "N") && (pd(t, n, l, r) && (n = null), r || l === null ? dd(t) && (n === null ? e.removeAttribute(t) : e.setAttribute(t, "" + n)) : l.mustUseProperty ? e[l.propertyName] = n === null ? l.type === 3 ? !1 : "" : n : (t = l.attributeName, r = l.attributeNamespace, n === null ? e.removeAttribute(t) : (l = l.type, n = l === 3 || l === 4 && n === !0 ? "" : "" + n, r ? e.setAttributeNS(r, t, n) : e.setAttribute(t, n))));
}
var ft = ad.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED, Nr = Symbol.for("react.element"), nn = Symbol.for("react.portal"), rn = Symbol.for("react.fragment"), vo = Symbol.for("react.strict_mode"), yi = Symbol.for("react.profiler"), Rs = Symbol.for("react.provider"), Os = Symbol.for("react.context"), yo = Symbol.for("react.forward_ref"), ki = Symbol.for("react.suspense"), wi = Symbol.for("react.suspense_list"), ko = Symbol.for("react.memo"), yt = Symbol.for("react.lazy"), Ds = Symbol.for("react.offscreen"), fu = Symbol.iterator;
function Rn(e) {
  return e === null || typeof e != "object" ? null : (e = fu && e[fu] || e["@@iterator"], typeof e == "function" ? e : null);
}
var q = Object.assign, Ql;
function Hn(e) {
  if (Ql === void 0) try {
    throw Error();
  } catch (n) {
    var t = n.stack.trim().match(/\n( *(at )?)/);
    Ql = t && t[1] || "";
  }
  return `
` + Ql + e;
}
var Kl = !1;
function Gl(e, t) {
  if (!e || Kl) return "";
  Kl = !0;
  var n = Error.prepareStackTrace;
  Error.prepareStackTrace = void 0;
  try {
    if (t) if (t = function() {
      throw Error();
    }, Object.defineProperty(t.prototype, "props", { set: function() {
      throw Error();
    } }), typeof Reflect == "object" && Reflect.construct) {
      try {
        Reflect.construct(t, []);
      } catch (c) {
        var r = c;
      }
      Reflect.construct(e, [], t);
    } else {
      try {
        t.call();
      } catch (c) {
        r = c;
      }
      e.call(t.prototype);
    }
    else {
      try {
        throw Error();
      } catch (c) {
        r = c;
      }
      e();
    }
  } catch (c) {
    if (c && r && typeof c.stack == "string") {
      for (var l = c.stack.split(`
`), i = r.stack.split(`
`), o = l.length - 1, u = i.length - 1; 1 <= o && 0 <= u && l[o] !== i[u]; ) u--;
      for (; 1 <= o && 0 <= u; o--, u--) if (l[o] !== i[u]) {
        if (o !== 1 || u !== 1)
          do
            if (o--, u--, 0 > u || l[o] !== i[u]) {
              var s = `
` + l[o].replace(" at new ", " at ");
              return e.displayName && s.includes("<anonymous>") && (s = s.replace("<anonymous>", e.displayName)), s;
            }
          while (1 <= o && 0 <= u);
        break;
      }
    }
  } finally {
    Kl = !1, Error.prepareStackTrace = n;
  }
  return (e = e ? e.displayName || e.name : "") ? Hn(e) : "";
}
function md(e) {
  switch (e.tag) {
    case 5:
      return Hn(e.type);
    case 16:
      return Hn("Lazy");
    case 13:
      return Hn("Suspense");
    case 19:
      return Hn("SuspenseList");
    case 0:
    case 2:
    case 15:
      return e = Gl(e.type, !1), e;
    case 11:
      return e = Gl(e.type.render, !1), e;
    case 1:
      return e = Gl(e.type, !0), e;
    default:
      return "";
  }
}
function Si(e) {
  if (e == null) return null;
  if (typeof e == "function") return e.displayName || e.name || null;
  if (typeof e == "string") return e;
  switch (e) {
    case rn:
      return "Fragment";
    case nn:
      return "Portal";
    case yi:
      return "Profiler";
    case vo:
      return "StrictMode";
    case ki:
      return "Suspense";
    case wi:
      return "SuspenseList";
  }
  if (typeof e == "object") switch (e.$$typeof) {
    case Os:
      return (e.displayName || "Context") + ".Consumer";
    case Rs:
      return (e._context.displayName || "Context") + ".Provider";
    case yo:
      var t = e.render;
      return e = e.displayName, e || (e = t.displayName || t.name || "", e = e !== "" ? "ForwardRef(" + e + ")" : "ForwardRef"), e;
    case ko:
      return t = e.displayName || null, t !== null ? t : Si(e.type) || "Memo";
    case yt:
      t = e._payload, e = e._init;
      try {
        return Si(e(t));
      } catch {
      }
  }
  return null;
}
function hd(e) {
  var t = e.type;
  switch (e.tag) {
    case 24:
      return "Cache";
    case 9:
      return (t.displayName || "Context") + ".Consumer";
    case 10:
      return (t._context.displayName || "Context") + ".Provider";
    case 18:
      return "DehydratedFragment";
    case 11:
      return e = t.render, e = e.displayName || e.name || "", t.displayName || (e !== "" ? "ForwardRef(" + e + ")" : "ForwardRef");
    case 7:
      return "Fragment";
    case 5:
      return t;
    case 4:
      return "Portal";
    case 3:
      return "Root";
    case 6:
      return "Text";
    case 16:
      return Si(t);
    case 8:
      return t === vo ? "StrictMode" : "Mode";
    case 22:
      return "Offscreen";
    case 12:
      return "Profiler";
    case 21:
      return "Scope";
    case 13:
      return "Suspense";
    case 19:
      return "SuspenseList";
    case 25:
      return "TracingMarker";
    case 1:
    case 0:
    case 17:
    case 2:
    case 14:
    case 15:
      if (typeof t == "function") return t.displayName || t.name || null;
      if (typeof t == "string") return t;
  }
  return null;
}
function Ft(e) {
  switch (typeof e) {
    case "boolean":
    case "number":
    case "string":
    case "undefined":
      return e;
    case "object":
      return e;
    default:
      return "";
  }
}
function Ws(e) {
  var t = e.type;
  return (e = e.nodeName) && e.toLowerCase() === "input" && (t === "checkbox" || t === "radio");
}
function gd(e) {
  var t = Ws(e) ? "checked" : "value", n = Object.getOwnPropertyDescriptor(e.constructor.prototype, t), r = "" + e[t];
  if (!e.hasOwnProperty(t) && typeof n < "u" && typeof n.get == "function" && typeof n.set == "function") {
    var l = n.get, i = n.set;
    return Object.defineProperty(e, t, { configurable: !0, get: function() {
      return l.call(this);
    }, set: function(o) {
      r = "" + o, i.call(this, o);
    } }), Object.defineProperty(e, t, { enumerable: n.enumerable }), { getValue: function() {
      return r;
    }, setValue: function(o) {
      r = "" + o;
    }, stopTracking: function() {
      e._valueTracker = null, delete e[t];
    } };
  }
}
function jr(e) {
  e._valueTracker || (e._valueTracker = gd(e));
}
function Is(e) {
  if (!e) return !1;
  var t = e._valueTracker;
  if (!t) return !0;
  var n = t.getValue(), r = "";
  return e && (r = Ws(e) ? e.checked ? "true" : "false" : e.value), e = r, e !== n ? (t.setValue(e), !0) : !1;
}
function nl(e) {
  if (e = e || (typeof document < "u" ? document : void 0), typeof e > "u") return null;
  try {
    return e.activeElement || e.body;
  } catch {
    return e.body;
  }
}
function xi(e, t) {
  var n = t.checked;
  return q({}, t, { defaultChecked: void 0, defaultValue: void 0, value: void 0, checked: n ?? e._wrapperState.initialChecked });
}
function pu(e, t) {
  var n = t.defaultValue == null ? "" : t.defaultValue, r = t.checked != null ? t.checked : t.defaultChecked;
  n = Ft(t.value != null ? t.value : n), e._wrapperState = { initialChecked: r, initialValue: n, controlled: t.type === "checkbox" || t.type === "radio" ? t.checked != null : t.value != null };
}
function Us(e, t) {
  t = t.checked, t != null && go(e, "checked", t, !1);
}
function _i(e, t) {
  Us(e, t);
  var n = Ft(t.value), r = t.type;
  if (n != null) r === "number" ? (n === 0 && e.value === "" || e.value != n) && (e.value = "" + n) : e.value !== "" + n && (e.value = "" + n);
  else if (r === "submit" || r === "reset") {
    e.removeAttribute("value");
    return;
  }
  t.hasOwnProperty("value") ? Ei(e, t.type, n) : t.hasOwnProperty("defaultValue") && Ei(e, t.type, Ft(t.defaultValue)), t.checked == null && t.defaultChecked != null && (e.defaultChecked = !!t.defaultChecked);
}
function mu(e, t, n) {
  if (t.hasOwnProperty("value") || t.hasOwnProperty("defaultValue")) {
    var r = t.type;
    if (!(r !== "submit" && r !== "reset" || t.value !== void 0 && t.value !== null)) return;
    t = "" + e._wrapperState.initialValue, n || t === e.value || (e.value = t), e.defaultValue = t;
  }
  n = e.name, n !== "" && (e.name = ""), e.defaultChecked = !!e._wrapperState.initialChecked, n !== "" && (e.name = n);
}
function Ei(e, t, n) {
  (t !== "number" || nl(e.ownerDocument) !== e) && (n == null ? e.defaultValue = "" + e._wrapperState.initialValue : e.defaultValue !== "" + n && (e.defaultValue = "" + n));
}
var Qn = Array.isArray;
function hn(e, t, n, r) {
  if (e = e.options, t) {
    t = {};
    for (var l = 0; l < n.length; l++) t["$" + n[l]] = !0;
    for (n = 0; n < e.length; n++) l = t.hasOwnProperty("$" + e[n].value), e[n].selected !== l && (e[n].selected = l), l && r && (e[n].defaultSelected = !0);
  } else {
    for (n = "" + Ft(n), t = null, l = 0; l < e.length; l++) {
      if (e[l].value === n) {
        e[l].selected = !0, r && (e[l].defaultSelected = !0);
        return;
      }
      t !== null || e[l].disabled || (t = e[l]);
    }
    t !== null && (t.selected = !0);
  }
}
function zi(e, t) {
  if (t.dangerouslySetInnerHTML != null) throw Error(y(91));
  return q({}, t, { value: void 0, defaultValue: void 0, children: "" + e._wrapperState.initialValue });
}
function hu(e, t) {
  var n = t.value;
  if (n == null) {
    if (n = t.children, t = t.defaultValue, n != null) {
      if (t != null) throw Error(y(92));
      if (Qn(n)) {
        if (1 < n.length) throw Error(y(93));
        n = n[0];
      }
      t = n;
    }
    t == null && (t = ""), n = t;
  }
  e._wrapperState = { initialValue: Ft(n) };
}
function $s(e, t) {
  var n = Ft(t.value), r = Ft(t.defaultValue);
  n != null && (n = "" + n, n !== e.value && (e.value = n), t.defaultValue == null && e.defaultValue !== n && (e.defaultValue = n)), r != null && (e.defaultValue = "" + r);
}
function gu(e) {
  var t = e.textContent;
  t === e._wrapperState.initialValue && t !== "" && t !== null && (e.value = t);
}
function Bs(e) {
  switch (e) {
    case "svg":
      return "http://www.w3.org/2000/svg";
    case "math":
      return "http://www.w3.org/1998/Math/MathML";
    default:
      return "http://www.w3.org/1999/xhtml";
  }
}
function Ci(e, t) {
  return e == null || e === "http://www.w3.org/1999/xhtml" ? Bs(t) : e === "http://www.w3.org/2000/svg" && t === "foreignObject" ? "http://www.w3.org/1999/xhtml" : e;
}
var Pr, Vs = function(e) {
  return typeof MSApp < "u" && MSApp.execUnsafeLocalFunction ? function(t, n, r, l) {
    MSApp.execUnsafeLocalFunction(function() {
      return e(t, n, r, l);
    });
  } : e;
}(function(e, t) {
  if (e.namespaceURI !== "http://www.w3.org/2000/svg" || "innerHTML" in e) e.innerHTML = t;
  else {
    for (Pr = Pr || document.createElement("div"), Pr.innerHTML = "<svg>" + t.valueOf().toString() + "</svg>", t = Pr.firstChild; e.firstChild; ) e.removeChild(e.firstChild);
    for (; t.firstChild; ) e.appendChild(t.firstChild);
  }
});
function lr(e, t) {
  if (t) {
    var n = e.firstChild;
    if (n && n === e.lastChild && n.nodeType === 3) {
      n.nodeValue = t;
      return;
    }
  }
  e.textContent = t;
}
var Yn = {
  animationIterationCount: !0,
  aspectRatio: !0,
  borderImageOutset: !0,
  borderImageSlice: !0,
  borderImageWidth: !0,
  boxFlex: !0,
  boxFlexGroup: !0,
  boxOrdinalGroup: !0,
  columnCount: !0,
  columns: !0,
  flex: !0,
  flexGrow: !0,
  flexPositive: !0,
  flexShrink: !0,
  flexNegative: !0,
  flexOrder: !0,
  gridArea: !0,
  gridRow: !0,
  gridRowEnd: !0,
  gridRowSpan: !0,
  gridRowStart: !0,
  gridColumn: !0,
  gridColumnEnd: !0,
  gridColumnSpan: !0,
  gridColumnStart: !0,
  fontWeight: !0,
  lineClamp: !0,
  lineHeight: !0,
  opacity: !0,
  order: !0,
  orphans: !0,
  tabSize: !0,
  widows: !0,
  zIndex: !0,
  zoom: !0,
  fillOpacity: !0,
  floodOpacity: !0,
  stopOpacity: !0,
  strokeDasharray: !0,
  strokeDashoffset: !0,
  strokeMiterlimit: !0,
  strokeOpacity: !0,
  strokeWidth: !0
}, vd = ["Webkit", "ms", "Moz", "O"];
Object.keys(Yn).forEach(function(e) {
  vd.forEach(function(t) {
    t = t + e.charAt(0).toUpperCase() + e.substring(1), Yn[t] = Yn[e];
  });
});
function Hs(e, t, n) {
  return t == null || typeof t == "boolean" || t === "" ? "" : n || typeof t != "number" || t === 0 || Yn.hasOwnProperty(e) && Yn[e] ? ("" + t).trim() : t + "px";
}
function Qs(e, t) {
  e = e.style;
  for (var n in t) if (t.hasOwnProperty(n)) {
    var r = n.indexOf("--") === 0, l = Hs(n, t[n], r);
    n === "float" && (n = "cssFloat"), r ? e.setProperty(n, l) : e[n] = l;
  }
}
var yd = q({ menuitem: !0 }, { area: !0, base: !0, br: !0, col: !0, embed: !0, hr: !0, img: !0, input: !0, keygen: !0, link: !0, meta: !0, param: !0, source: !0, track: !0, wbr: !0 });
function Ni(e, t) {
  if (t) {
    if (yd[e] && (t.children != null || t.dangerouslySetInnerHTML != null)) throw Error(y(137, e));
    if (t.dangerouslySetInnerHTML != null) {
      if (t.children != null) throw Error(y(60));
      if (typeof t.dangerouslySetInnerHTML != "object" || !("__html" in t.dangerouslySetInnerHTML)) throw Error(y(61));
    }
    if (t.style != null && typeof t.style != "object") throw Error(y(62));
  }
}
function ji(e, t) {
  if (e.indexOf("-") === -1) return typeof t.is == "string";
  switch (e) {
    case "annotation-xml":
    case "color-profile":
    case "font-face":
    case "font-face-src":
    case "font-face-uri":
    case "font-face-format":
    case "font-face-name":
    case "missing-glyph":
      return !1;
    default:
      return !0;
  }
}
var Pi = null;
function wo(e) {
  return e = e.target || e.srcElement || window, e.correspondingUseElement && (e = e.correspondingUseElement), e.nodeType === 3 ? e.parentNode : e;
}
var Ti = null, gn = null, vn = null;
function vu(e) {
  if (e = _r(e)) {
    if (typeof Ti != "function") throw Error(y(280));
    var t = e.stateNode;
    t && (t = Ll(t), Ti(e.stateNode, e.type, t));
  }
}
function Ks(e) {
  gn ? vn ? vn.push(e) : vn = [e] : gn = e;
}
function Gs() {
  if (gn) {
    var e = gn, t = vn;
    if (vn = gn = null, vu(e), t) for (e = 0; e < t.length; e++) vu(t[e]);
  }
}
function Ys(e, t) {
  return e(t);
}
function Xs() {
}
var Yl = !1;
function Zs(e, t, n) {
  if (Yl) return e(t, n);
  Yl = !0;
  try {
    return Ys(e, t, n);
  } finally {
    Yl = !1, (gn !== null || vn !== null) && (Xs(), Gs());
  }
}
function ir(e, t) {
  var n = e.stateNode;
  if (n === null) return null;
  var r = Ll(n);
  if (r === null) return null;
  n = r[t];
  e: switch (t) {
    case "onClick":
    case "onClickCapture":
    case "onDoubleClick":
    case "onDoubleClickCapture":
    case "onMouseDown":
    case "onMouseDownCapture":
    case "onMouseMove":
    case "onMouseMoveCapture":
    case "onMouseUp":
    case "onMouseUpCapture":
    case "onMouseEnter":
      (r = !r.disabled) || (e = e.type, r = !(e === "button" || e === "input" || e === "select" || e === "textarea")), e = !r;
      break e;
    default:
      e = !1;
  }
  if (e) return null;
  if (n && typeof n != "function") throw Error(y(231, t, typeof n));
  return n;
}
var Li = !1;
if (st) try {
  var On = {};
  Object.defineProperty(On, "passive", { get: function() {
    Li = !0;
  } }), window.addEventListener("test", On, On), window.removeEventListener("test", On, On);
} catch {
  Li = !1;
}
function kd(e, t, n, r, l, i, o, u, s) {
  var c = Array.prototype.slice.call(arguments, 3);
  try {
    t.apply(n, c);
  } catch (m) {
    this.onError(m);
  }
}
var Xn = !1, rl = null, ll = !1, Fi = null, wd = { onError: function(e) {
  Xn = !0, rl = e;
} };
function Sd(e, t, n, r, l, i, o, u, s) {
  Xn = !1, rl = null, kd.apply(wd, arguments);
}
function xd(e, t, n, r, l, i, o, u, s) {
  if (Sd.apply(this, arguments), Xn) {
    if (Xn) {
      var c = rl;
      Xn = !1, rl = null;
    } else throw Error(y(198));
    ll || (ll = !0, Fi = c);
  }
}
function Jt(e) {
  var t = e, n = e;
  if (e.alternate) for (; t.return; ) t = t.return;
  else {
    e = t;
    do
      t = e, t.flags & 4098 && (n = t.return), e = t.return;
    while (e);
  }
  return t.tag === 3 ? n : null;
}
function Js(e) {
  if (e.tag === 13) {
    var t = e.memoizedState;
    if (t === null && (e = e.alternate, e !== null && (t = e.memoizedState)), t !== null) return t.dehydrated;
  }
  return null;
}
function yu(e) {
  if (Jt(e) !== e) throw Error(y(188));
}
function _d(e) {
  var t = e.alternate;
  if (!t) {
    if (t = Jt(e), t === null) throw Error(y(188));
    return t !== e ? null : e;
  }
  for (var n = e, r = t; ; ) {
    var l = n.return;
    if (l === null) break;
    var i = l.alternate;
    if (i === null) {
      if (r = l.return, r !== null) {
        n = r;
        continue;
      }
      break;
    }
    if (l.child === i.child) {
      for (i = l.child; i; ) {
        if (i === n) return yu(l), e;
        if (i === r) return yu(l), t;
        i = i.sibling;
      }
      throw Error(y(188));
    }
    if (n.return !== r.return) n = l, r = i;
    else {
      for (var o = !1, u = l.child; u; ) {
        if (u === n) {
          o = !0, n = l, r = i;
          break;
        }
        if (u === r) {
          o = !0, r = l, n = i;
          break;
        }
        u = u.sibling;
      }
      if (!o) {
        for (u = i.child; u; ) {
          if (u === n) {
            o = !0, n = i, r = l;
            break;
          }
          if (u === r) {
            o = !0, r = i, n = l;
            break;
          }
          u = u.sibling;
        }
        if (!o) throw Error(y(189));
      }
    }
    if (n.alternate !== r) throw Error(y(190));
  }
  if (n.tag !== 3) throw Error(y(188));
  return n.stateNode.current === n ? e : t;
}
function qs(e) {
  return e = _d(e), e !== null ? bs(e) : null;
}
function bs(e) {
  if (e.tag === 5 || e.tag === 6) return e;
  for (e = e.child; e !== null; ) {
    var t = bs(e);
    if (t !== null) return t;
    e = e.sibling;
  }
  return null;
}
var ea = Pe.unstable_scheduleCallback, ku = Pe.unstable_cancelCallback, Ed = Pe.unstable_shouldYield, zd = Pe.unstable_requestPaint, ee = Pe.unstable_now, Cd = Pe.unstable_getCurrentPriorityLevel, So = Pe.unstable_ImmediatePriority, ta = Pe.unstable_UserBlockingPriority, il = Pe.unstable_NormalPriority, Nd = Pe.unstable_LowPriority, na = Pe.unstable_IdlePriority, Nl = null, Je = null;
function jd(e) {
  if (Je && typeof Je.onCommitFiberRoot == "function") try {
    Je.onCommitFiberRoot(Nl, e, void 0, (e.current.flags & 128) === 128);
  } catch {
  }
}
var Ve = Math.clz32 ? Math.clz32 : Ld, Pd = Math.log, Td = Math.LN2;
function Ld(e) {
  return e >>>= 0, e === 0 ? 32 : 31 - (Pd(e) / Td | 0) | 0;
}
var Tr = 64, Lr = 4194304;
function Kn(e) {
  switch (e & -e) {
    case 1:
      return 1;
    case 2:
      return 2;
    case 4:
      return 4;
    case 8:
      return 8;
    case 16:
      return 16;
    case 32:
      return 32;
    case 64:
    case 128:
    case 256:
    case 512:
    case 1024:
    case 2048:
    case 4096:
    case 8192:
    case 16384:
    case 32768:
    case 65536:
    case 131072:
    case 262144:
    case 524288:
    case 1048576:
    case 2097152:
      return e & 4194240;
    case 4194304:
    case 8388608:
    case 16777216:
    case 33554432:
    case 67108864:
      return e & 130023424;
    case 134217728:
      return 134217728;
    case 268435456:
      return 268435456;
    case 536870912:
      return 536870912;
    case 1073741824:
      return 1073741824;
    default:
      return e;
  }
}
function ol(e, t) {
  var n = e.pendingLanes;
  if (n === 0) return 0;
  var r = 0, l = e.suspendedLanes, i = e.pingedLanes, o = n & 268435455;
  if (o !== 0) {
    var u = o & ~l;
    u !== 0 ? r = Kn(u) : (i &= o, i !== 0 && (r = Kn(i)));
  } else o = n & ~l, o !== 0 ? r = Kn(o) : i !== 0 && (r = Kn(i));
  if (r === 0) return 0;
  if (t !== 0 && t !== r && !(t & l) && (l = r & -r, i = t & -t, l >= i || l === 16 && (i & 4194240) !== 0)) return t;
  if (r & 4 && (r |= n & 16), t = e.entangledLanes, t !== 0) for (e = e.entanglements, t &= r; 0 < t; ) n = 31 - Ve(t), l = 1 << n, r |= e[n], t &= ~l;
  return r;
}
function Fd(e, t) {
  switch (e) {
    case 1:
    case 2:
    case 4:
      return t + 250;
    case 8:
    case 16:
    case 32:
    case 64:
    case 128:
    case 256:
    case 512:
    case 1024:
    case 2048:
    case 4096:
    case 8192:
    case 16384:
    case 32768:
    case 65536:
    case 131072:
    case 262144:
    case 524288:
    case 1048576:
    case 2097152:
      return t + 5e3;
    case 4194304:
    case 8388608:
    case 16777216:
    case 33554432:
    case 67108864:
      return -1;
    case 134217728:
    case 268435456:
    case 536870912:
    case 1073741824:
      return -1;
    default:
      return -1;
  }
}
function Md(e, t) {
  for (var n = e.suspendedLanes, r = e.pingedLanes, l = e.expirationTimes, i = e.pendingLanes; 0 < i; ) {
    var o = 31 - Ve(i), u = 1 << o, s = l[o];
    s === -1 ? (!(u & n) || u & r) && (l[o] = Fd(u, t)) : s <= t && (e.expiredLanes |= u), i &= ~u;
  }
}
function Mi(e) {
  return e = e.pendingLanes & -1073741825, e !== 0 ? e : e & 1073741824 ? 1073741824 : 0;
}
function ra() {
  var e = Tr;
  return Tr <<= 1, !(Tr & 4194240) && (Tr = 64), e;
}
function Xl(e) {
  for (var t = [], n = 0; 31 > n; n++) t.push(e);
  return t;
}
function Sr(e, t, n) {
  e.pendingLanes |= t, t !== 536870912 && (e.suspendedLanes = 0, e.pingedLanes = 0), e = e.eventTimes, t = 31 - Ve(t), e[t] = n;
}
function Ad(e, t) {
  var n = e.pendingLanes & ~t;
  e.pendingLanes = t, e.suspendedLanes = 0, e.pingedLanes = 0, e.expiredLanes &= t, e.mutableReadLanes &= t, e.entangledLanes &= t, t = e.entanglements;
  var r = e.eventTimes;
  for (e = e.expirationTimes; 0 < n; ) {
    var l = 31 - Ve(n), i = 1 << l;
    t[l] = 0, r[l] = -1, e[l] = -1, n &= ~i;
  }
}
function xo(e, t) {
  var n = e.entangledLanes |= t;
  for (e = e.entanglements; n; ) {
    var r = 31 - Ve(n), l = 1 << r;
    l & t | e[r] & t && (e[r] |= t), n &= ~l;
  }
}
var I = 0;
function la(e) {
  return e &= -e, 1 < e ? 4 < e ? e & 268435455 ? 16 : 536870912 : 4 : 1;
}
var ia, _o, oa, ua, sa, Ai = !1, Fr = [], Et = null, zt = null, Ct = null, or = /* @__PURE__ */ new Map(), ur = /* @__PURE__ */ new Map(), wt = [], Rd = "mousedown mouseup touchcancel touchend touchstart auxclick dblclick pointercancel pointerdown pointerup dragend dragstart drop compositionend compositionstart keydown keypress keyup input textInput copy cut paste click change contextmenu reset submit".split(" ");
function wu(e, t) {
  switch (e) {
    case "focusin":
    case "focusout":
      Et = null;
      break;
    case "dragenter":
    case "dragleave":
      zt = null;
      break;
    case "mouseover":
    case "mouseout":
      Ct = null;
      break;
    case "pointerover":
    case "pointerout":
      or.delete(t.pointerId);
      break;
    case "gotpointercapture":
    case "lostpointercapture":
      ur.delete(t.pointerId);
  }
}
function Dn(e, t, n, r, l, i) {
  return e === null || e.nativeEvent !== i ? (e = { blockedOn: t, domEventName: n, eventSystemFlags: r, nativeEvent: i, targetContainers: [l] }, t !== null && (t = _r(t), t !== null && _o(t)), e) : (e.eventSystemFlags |= r, t = e.targetContainers, l !== null && t.indexOf(l) === -1 && t.push(l), e);
}
function Od(e, t, n, r, l) {
  switch (t) {
    case "focusin":
      return Et = Dn(Et, e, t, n, r, l), !0;
    case "dragenter":
      return zt = Dn(zt, e, t, n, r, l), !0;
    case "mouseover":
      return Ct = Dn(Ct, e, t, n, r, l), !0;
    case "pointerover":
      var i = l.pointerId;
      return or.set(i, Dn(or.get(i) || null, e, t, n, r, l)), !0;
    case "gotpointercapture":
      return i = l.pointerId, ur.set(i, Dn(ur.get(i) || null, e, t, n, r, l)), !0;
  }
  return !1;
}
function aa(e) {
  var t = Ut(e.target);
  if (t !== null) {
    var n = Jt(t);
    if (n !== null) {
      if (t = n.tag, t === 13) {
        if (t = Js(n), t !== null) {
          e.blockedOn = t, sa(e.priority, function() {
            oa(n);
          });
          return;
        }
      } else if (t === 3 && n.stateNode.current.memoizedState.isDehydrated) {
        e.blockedOn = n.tag === 3 ? n.stateNode.containerInfo : null;
        return;
      }
    }
  }
  e.blockedOn = null;
}
function Kr(e) {
  if (e.blockedOn !== null) return !1;
  for (var t = e.targetContainers; 0 < t.length; ) {
    var n = Ri(e.domEventName, e.eventSystemFlags, t[0], e.nativeEvent);
    if (n === null) {
      n = e.nativeEvent;
      var r = new n.constructor(n.type, n);
      Pi = r, n.target.dispatchEvent(r), Pi = null;
    } else return t = _r(n), t !== null && _o(t), e.blockedOn = n, !1;
    t.shift();
  }
  return !0;
}
function Su(e, t, n) {
  Kr(e) && n.delete(t);
}
function Dd() {
  Ai = !1, Et !== null && Kr(Et) && (Et = null), zt !== null && Kr(zt) && (zt = null), Ct !== null && Kr(Ct) && (Ct = null), or.forEach(Su), ur.forEach(Su);
}
function Wn(e, t) {
  e.blockedOn === t && (e.blockedOn = null, Ai || (Ai = !0, Pe.unstable_scheduleCallback(Pe.unstable_NormalPriority, Dd)));
}
function sr(e) {
  function t(l) {
    return Wn(l, e);
  }
  if (0 < Fr.length) {
    Wn(Fr[0], e);
    for (var n = 1; n < Fr.length; n++) {
      var r = Fr[n];
      r.blockedOn === e && (r.blockedOn = null);
    }
  }
  for (Et !== null && Wn(Et, e), zt !== null && Wn(zt, e), Ct !== null && Wn(Ct, e), or.forEach(t), ur.forEach(t), n = 0; n < wt.length; n++) r = wt[n], r.blockedOn === e && (r.blockedOn = null);
  for (; 0 < wt.length && (n = wt[0], n.blockedOn === null); ) aa(n), n.blockedOn === null && wt.shift();
}
var yn = ft.ReactCurrentBatchConfig, ul = !0;
function Wd(e, t, n, r) {
  var l = I, i = yn.transition;
  yn.transition = null;
  try {
    I = 1, Eo(e, t, n, r);
  } finally {
    I = l, yn.transition = i;
  }
}
function Id(e, t, n, r) {
  var l = I, i = yn.transition;
  yn.transition = null;
  try {
    I = 4, Eo(e, t, n, r);
  } finally {
    I = l, yn.transition = i;
  }
}
function Eo(e, t, n, r) {
  if (ul) {
    var l = Ri(e, t, n, r);
    if (l === null) ii(e, t, r, sl, n), wu(e, r);
    else if (Od(l, e, t, n, r)) r.stopPropagation();
    else if (wu(e, r), t & 4 && -1 < Rd.indexOf(e)) {
      for (; l !== null; ) {
        var i = _r(l);
        if (i !== null && ia(i), i = Ri(e, t, n, r), i === null && ii(e, t, r, sl, n), i === l) break;
        l = i;
      }
      l !== null && r.stopPropagation();
    } else ii(e, t, r, null, n);
  }
}
var sl = null;
function Ri(e, t, n, r) {
  if (sl = null, e = wo(r), e = Ut(e), e !== null) if (t = Jt(e), t === null) e = null;
  else if (n = t.tag, n === 13) {
    if (e = Js(t), e !== null) return e;
    e = null;
  } else if (n === 3) {
    if (t.stateNode.current.memoizedState.isDehydrated) return t.tag === 3 ? t.stateNode.containerInfo : null;
    e = null;
  } else t !== e && (e = null);
  return sl = e, null;
}
function ca(e) {
  switch (e) {
    case "cancel":
    case "click":
    case "close":
    case "contextmenu":
    case "copy":
    case "cut":
    case "auxclick":
    case "dblclick":
    case "dragend":
    case "dragstart":
    case "drop":
    case "focusin":
    case "focusout":
    case "input":
    case "invalid":
    case "keydown":
    case "keypress":
    case "keyup":
    case "mousedown":
    case "mouseup":
    case "paste":
    case "pause":
    case "play":
    case "pointercancel":
    case "pointerdown":
    case "pointerup":
    case "ratechange":
    case "reset":
    case "resize":
    case "seeked":
    case "submit":
    case "touchcancel":
    case "touchend":
    case "touchstart":
    case "volumechange":
    case "change":
    case "selectionchange":
    case "textInput":
    case "compositionstart":
    case "compositionend":
    case "compositionupdate":
    case "beforeblur":
    case "afterblur":
    case "beforeinput":
    case "blur":
    case "fullscreenchange":
    case "focus":
    case "hashchange":
    case "popstate":
    case "select":
    case "selectstart":
      return 1;
    case "drag":
    case "dragenter":
    case "dragexit":
    case "dragleave":
    case "dragover":
    case "mousemove":
    case "mouseout":
    case "mouseover":
    case "pointermove":
    case "pointerout":
    case "pointerover":
    case "scroll":
    case "toggle":
    case "touchmove":
    case "wheel":
    case "mouseenter":
    case "mouseleave":
    case "pointerenter":
    case "pointerleave":
      return 4;
    case "message":
      switch (Cd()) {
        case So:
          return 1;
        case ta:
          return 4;
        case il:
        case Nd:
          return 16;
        case na:
          return 536870912;
        default:
          return 16;
      }
    default:
      return 16;
  }
}
var xt = null, zo = null, Gr = null;
function da() {
  if (Gr) return Gr;
  var e, t = zo, n = t.length, r, l = "value" in xt ? xt.value : xt.textContent, i = l.length;
  for (e = 0; e < n && t[e] === l[e]; e++) ;
  var o = n - e;
  for (r = 1; r <= o && t[n - r] === l[i - r]; r++) ;
  return Gr = l.slice(e, 1 < r ? 1 - r : void 0);
}
function Yr(e) {
  var t = e.keyCode;
  return "charCode" in e ? (e = e.charCode, e === 0 && t === 13 && (e = 13)) : e = t, e === 10 && (e = 13), 32 <= e || e === 13 ? e : 0;
}
function Mr() {
  return !0;
}
function xu() {
  return !1;
}
function Le(e) {
  function t(n, r, l, i, o) {
    this._reactName = n, this._targetInst = l, this.type = r, this.nativeEvent = i, this.target = o, this.currentTarget = null;
    for (var u in e) e.hasOwnProperty(u) && (n = e[u], this[u] = n ? n(i) : i[u]);
    return this.isDefaultPrevented = (i.defaultPrevented != null ? i.defaultPrevented : i.returnValue === !1) ? Mr : xu, this.isPropagationStopped = xu, this;
  }
  return q(t.prototype, { preventDefault: function() {
    this.defaultPrevented = !0;
    var n = this.nativeEvent;
    n && (n.preventDefault ? n.preventDefault() : typeof n.returnValue != "unknown" && (n.returnValue = !1), this.isDefaultPrevented = Mr);
  }, stopPropagation: function() {
    var n = this.nativeEvent;
    n && (n.stopPropagation ? n.stopPropagation() : typeof n.cancelBubble != "unknown" && (n.cancelBubble = !0), this.isPropagationStopped = Mr);
  }, persist: function() {
  }, isPersistent: Mr }), t;
}
var Tn = { eventPhase: 0, bubbles: 0, cancelable: 0, timeStamp: function(e) {
  return e.timeStamp || Date.now();
}, defaultPrevented: 0, isTrusted: 0 }, Co = Le(Tn), xr = q({}, Tn, { view: 0, detail: 0 }), Ud = Le(xr), Zl, Jl, In, jl = q({}, xr, { screenX: 0, screenY: 0, clientX: 0, clientY: 0, pageX: 0, pageY: 0, ctrlKey: 0, shiftKey: 0, altKey: 0, metaKey: 0, getModifierState: No, button: 0, buttons: 0, relatedTarget: function(e) {
  return e.relatedTarget === void 0 ? e.fromElement === e.srcElement ? e.toElement : e.fromElement : e.relatedTarget;
}, movementX: function(e) {
  return "movementX" in e ? e.movementX : (e !== In && (In && e.type === "mousemove" ? (Zl = e.screenX - In.screenX, Jl = e.screenY - In.screenY) : Jl = Zl = 0, In = e), Zl);
}, movementY: function(e) {
  return "movementY" in e ? e.movementY : Jl;
} }), _u = Le(jl), $d = q({}, jl, { dataTransfer: 0 }), Bd = Le($d), Vd = q({}, xr, { relatedTarget: 0 }), ql = Le(Vd), Hd = q({}, Tn, { animationName: 0, elapsedTime: 0, pseudoElement: 0 }), Qd = Le(Hd), Kd = q({}, Tn, { clipboardData: function(e) {
  return "clipboardData" in e ? e.clipboardData : window.clipboardData;
} }), Gd = Le(Kd), Yd = q({}, Tn, { data: 0 }), Eu = Le(Yd), Xd = {
  Esc: "Escape",
  Spacebar: " ",
  Left: "ArrowLeft",
  Up: "ArrowUp",
  Right: "ArrowRight",
  Down: "ArrowDown",
  Del: "Delete",
  Win: "OS",
  Menu: "ContextMenu",
  Apps: "ContextMenu",
  Scroll: "ScrollLock",
  MozPrintableKey: "Unidentified"
}, Zd = {
  8: "Backspace",
  9: "Tab",
  12: "Clear",
  13: "Enter",
  16: "Shift",
  17: "Control",
  18: "Alt",
  19: "Pause",
  20: "CapsLock",
  27: "Escape",
  32: " ",
  33: "PageUp",
  34: "PageDown",
  35: "End",
  36: "Home",
  37: "ArrowLeft",
  38: "ArrowUp",
  39: "ArrowRight",
  40: "ArrowDown",
  45: "Insert",
  46: "Delete",
  112: "F1",
  113: "F2",
  114: "F3",
  115: "F4",
  116: "F5",
  117: "F6",
  118: "F7",
  119: "F8",
  120: "F9",
  121: "F10",
  122: "F11",
  123: "F12",
  144: "NumLock",
  145: "ScrollLock",
  224: "Meta"
}, Jd = { Alt: "altKey", Control: "ctrlKey", Meta: "metaKey", Shift: "shiftKey" };
function qd(e) {
  var t = this.nativeEvent;
  return t.getModifierState ? t.getModifierState(e) : (e = Jd[e]) ? !!t[e] : !1;
}
function No() {
  return qd;
}
var bd = q({}, xr, { key: function(e) {
  if (e.key) {
    var t = Xd[e.key] || e.key;
    if (t !== "Unidentified") return t;
  }
  return e.type === "keypress" ? (e = Yr(e), e === 13 ? "Enter" : String.fromCharCode(e)) : e.type === "keydown" || e.type === "keyup" ? Zd[e.keyCode] || "Unidentified" : "";
}, code: 0, location: 0, ctrlKey: 0, shiftKey: 0, altKey: 0, metaKey: 0, repeat: 0, locale: 0, getModifierState: No, charCode: function(e) {
  return e.type === "keypress" ? Yr(e) : 0;
}, keyCode: function(e) {
  return e.type === "keydown" || e.type === "keyup" ? e.keyCode : 0;
}, which: function(e) {
  return e.type === "keypress" ? Yr(e) : e.type === "keydown" || e.type === "keyup" ? e.keyCode : 0;
} }), ef = Le(bd), tf = q({}, jl, { pointerId: 0, width: 0, height: 0, pressure: 0, tangentialPressure: 0, tiltX: 0, tiltY: 0, twist: 0, pointerType: 0, isPrimary: 0 }), zu = Le(tf), nf = q({}, xr, { touches: 0, targetTouches: 0, changedTouches: 0, altKey: 0, metaKey: 0, ctrlKey: 0, shiftKey: 0, getModifierState: No }), rf = Le(nf), lf = q({}, Tn, { propertyName: 0, elapsedTime: 0, pseudoElement: 0 }), of = Le(lf), uf = q({}, jl, {
  deltaX: function(e) {
    return "deltaX" in e ? e.deltaX : "wheelDeltaX" in e ? -e.wheelDeltaX : 0;
  },
  deltaY: function(e) {
    return "deltaY" in e ? e.deltaY : "wheelDeltaY" in e ? -e.wheelDeltaY : "wheelDelta" in e ? -e.wheelDelta : 0;
  },
  deltaZ: 0,
  deltaMode: 0
}), sf = Le(uf), af = [9, 13, 27, 32], jo = st && "CompositionEvent" in window, Zn = null;
st && "documentMode" in document && (Zn = document.documentMode);
var cf = st && "TextEvent" in window && !Zn, fa = st && (!jo || Zn && 8 < Zn && 11 >= Zn), Cu = " ", Nu = !1;
function pa(e, t) {
  switch (e) {
    case "keyup":
      return af.indexOf(t.keyCode) !== -1;
    case "keydown":
      return t.keyCode !== 229;
    case "keypress":
    case "mousedown":
    case "focusout":
      return !0;
    default:
      return !1;
  }
}
function ma(e) {
  return e = e.detail, typeof e == "object" && "data" in e ? e.data : null;
}
var ln = !1;
function df(e, t) {
  switch (e) {
    case "compositionend":
      return ma(t);
    case "keypress":
      return t.which !== 32 ? null : (Nu = !0, Cu);
    case "textInput":
      return e = t.data, e === Cu && Nu ? null : e;
    default:
      return null;
  }
}
function ff(e, t) {
  if (ln) return e === "compositionend" || !jo && pa(e, t) ? (e = da(), Gr = zo = xt = null, ln = !1, e) : null;
  switch (e) {
    case "paste":
      return null;
    case "keypress":
      if (!(t.ctrlKey || t.altKey || t.metaKey) || t.ctrlKey && t.altKey) {
        if (t.char && 1 < t.char.length) return t.char;
        if (t.which) return String.fromCharCode(t.which);
      }
      return null;
    case "compositionend":
      return fa && t.locale !== "ko" ? null : t.data;
    default:
      return null;
  }
}
var pf = { color: !0, date: !0, datetime: !0, "datetime-local": !0, email: !0, month: !0, number: !0, password: !0, range: !0, search: !0, tel: !0, text: !0, time: !0, url: !0, week: !0 };
function ju(e) {
  var t = e && e.nodeName && e.nodeName.toLowerCase();
  return t === "input" ? !!pf[e.type] : t === "textarea";
}
function ha(e, t, n, r) {
  Ks(r), t = al(t, "onChange"), 0 < t.length && (n = new Co("onChange", "change", null, n, r), e.push({ event: n, listeners: t }));
}
var Jn = null, ar = null;
function mf(e) {
  Ca(e, 0);
}
function Pl(e) {
  var t = sn(e);
  if (Is(t)) return e;
}
function hf(e, t) {
  if (e === "change") return t;
}
var ga = !1;
if (st) {
  var bl;
  if (st) {
    var ei = "oninput" in document;
    if (!ei) {
      var Pu = document.createElement("div");
      Pu.setAttribute("oninput", "return;"), ei = typeof Pu.oninput == "function";
    }
    bl = ei;
  } else bl = !1;
  ga = bl && (!document.documentMode || 9 < document.documentMode);
}
function Tu() {
  Jn && (Jn.detachEvent("onpropertychange", va), ar = Jn = null);
}
function va(e) {
  if (e.propertyName === "value" && Pl(ar)) {
    var t = [];
    ha(t, ar, e, wo(e)), Zs(mf, t);
  }
}
function gf(e, t, n) {
  e === "focusin" ? (Tu(), Jn = t, ar = n, Jn.attachEvent("onpropertychange", va)) : e === "focusout" && Tu();
}
function vf(e) {
  if (e === "selectionchange" || e === "keyup" || e === "keydown") return Pl(ar);
}
function yf(e, t) {
  if (e === "click") return Pl(t);
}
function kf(e, t) {
  if (e === "input" || e === "change") return Pl(t);
}
function wf(e, t) {
  return e === t && (e !== 0 || 1 / e === 1 / t) || e !== e && t !== t;
}
var Qe = typeof Object.is == "function" ? Object.is : wf;
function cr(e, t) {
  if (Qe(e, t)) return !0;
  if (typeof e != "object" || e === null || typeof t != "object" || t === null) return !1;
  var n = Object.keys(e), r = Object.keys(t);
  if (n.length !== r.length) return !1;
  for (r = 0; r < n.length; r++) {
    var l = n[r];
    if (!vi.call(t, l) || !Qe(e[l], t[l])) return !1;
  }
  return !0;
}
function Lu(e) {
  for (; e && e.firstChild; ) e = e.firstChild;
  return e;
}
function Fu(e, t) {
  var n = Lu(e);
  e = 0;
  for (var r; n; ) {
    if (n.nodeType === 3) {
      if (r = e + n.textContent.length, e <= t && r >= t) return { node: n, offset: t - e };
      e = r;
    }
    e: {
      for (; n; ) {
        if (n.nextSibling) {
          n = n.nextSibling;
          break e;
        }
        n = n.parentNode;
      }
      n = void 0;
    }
    n = Lu(n);
  }
}
function ya(e, t) {
  return e && t ? e === t ? !0 : e && e.nodeType === 3 ? !1 : t && t.nodeType === 3 ? ya(e, t.parentNode) : "contains" in e ? e.contains(t) : e.compareDocumentPosition ? !!(e.compareDocumentPosition(t) & 16) : !1 : !1;
}
function ka() {
  for (var e = window, t = nl(); t instanceof e.HTMLIFrameElement; ) {
    try {
      var n = typeof t.contentWindow.location.href == "string";
    } catch {
      n = !1;
    }
    if (n) e = t.contentWindow;
    else break;
    t = nl(e.document);
  }
  return t;
}
function Po(e) {
  var t = e && e.nodeName && e.nodeName.toLowerCase();
  return t && (t === "input" && (e.type === "text" || e.type === "search" || e.type === "tel" || e.type === "url" || e.type === "password") || t === "textarea" || e.contentEditable === "true");
}
function Sf(e) {
  var t = ka(), n = e.focusedElem, r = e.selectionRange;
  if (t !== n && n && n.ownerDocument && ya(n.ownerDocument.documentElement, n)) {
    if (r !== null && Po(n)) {
      if (t = r.start, e = r.end, e === void 0 && (e = t), "selectionStart" in n) n.selectionStart = t, n.selectionEnd = Math.min(e, n.value.length);
      else if (e = (t = n.ownerDocument || document) && t.defaultView || window, e.getSelection) {
        e = e.getSelection();
        var l = n.textContent.length, i = Math.min(r.start, l);
        r = r.end === void 0 ? i : Math.min(r.end, l), !e.extend && i > r && (l = r, r = i, i = l), l = Fu(n, i);
        var o = Fu(
          n,
          r
        );
        l && o && (e.rangeCount !== 1 || e.anchorNode !== l.node || e.anchorOffset !== l.offset || e.focusNode !== o.node || e.focusOffset !== o.offset) && (t = t.createRange(), t.setStart(l.node, l.offset), e.removeAllRanges(), i > r ? (e.addRange(t), e.extend(o.node, o.offset)) : (t.setEnd(o.node, o.offset), e.addRange(t)));
      }
    }
    for (t = [], e = n; e = e.parentNode; ) e.nodeType === 1 && t.push({ element: e, left: e.scrollLeft, top: e.scrollTop });
    for (typeof n.focus == "function" && n.focus(), n = 0; n < t.length; n++) e = t[n], e.element.scrollLeft = e.left, e.element.scrollTop = e.top;
  }
}
var xf = st && "documentMode" in document && 11 >= document.documentMode, on = null, Oi = null, qn = null, Di = !1;
function Mu(e, t, n) {
  var r = n.window === n ? n.document : n.nodeType === 9 ? n : n.ownerDocument;
  Di || on == null || on !== nl(r) || (r = on, "selectionStart" in r && Po(r) ? r = { start: r.selectionStart, end: r.selectionEnd } : (r = (r.ownerDocument && r.ownerDocument.defaultView || window).getSelection(), r = { anchorNode: r.anchorNode, anchorOffset: r.anchorOffset, focusNode: r.focusNode, focusOffset: r.focusOffset }), qn && cr(qn, r) || (qn = r, r = al(Oi, "onSelect"), 0 < r.length && (t = new Co("onSelect", "select", null, t, n), e.push({ event: t, listeners: r }), t.target = on)));
}
function Ar(e, t) {
  var n = {};
  return n[e.toLowerCase()] = t.toLowerCase(), n["Webkit" + e] = "webkit" + t, n["Moz" + e] = "moz" + t, n;
}
var un = { animationend: Ar("Animation", "AnimationEnd"), animationiteration: Ar("Animation", "AnimationIteration"), animationstart: Ar("Animation", "AnimationStart"), transitionend: Ar("Transition", "TransitionEnd") }, ti = {}, wa = {};
st && (wa = document.createElement("div").style, "AnimationEvent" in window || (delete un.animationend.animation, delete un.animationiteration.animation, delete un.animationstart.animation), "TransitionEvent" in window || delete un.transitionend.transition);
function Tl(e) {
  if (ti[e]) return ti[e];
  if (!un[e]) return e;
  var t = un[e], n;
  for (n in t) if (t.hasOwnProperty(n) && n in wa) return ti[e] = t[n];
  return e;
}
var Sa = Tl("animationend"), xa = Tl("animationiteration"), _a = Tl("animationstart"), Ea = Tl("transitionend"), za = /* @__PURE__ */ new Map(), Au = "abort auxClick cancel canPlay canPlayThrough click close contextMenu copy cut drag dragEnd dragEnter dragExit dragLeave dragOver dragStart drop durationChange emptied encrypted ended error gotPointerCapture input invalid keyDown keyPress keyUp load loadedData loadedMetadata loadStart lostPointerCapture mouseDown mouseMove mouseOut mouseOver mouseUp paste pause play playing pointerCancel pointerDown pointerMove pointerOut pointerOver pointerUp progress rateChange reset resize seeked seeking stalled submit suspend timeUpdate touchCancel touchEnd touchStart volumeChange scroll toggle touchMove waiting wheel".split(" ");
function At(e, t) {
  za.set(e, t), Zt(t, [e]);
}
for (var ni = 0; ni < Au.length; ni++) {
  var ri = Au[ni], _f = ri.toLowerCase(), Ef = ri[0].toUpperCase() + ri.slice(1);
  At(_f, "on" + Ef);
}
At(Sa, "onAnimationEnd");
At(xa, "onAnimationIteration");
At(_a, "onAnimationStart");
At("dblclick", "onDoubleClick");
At("focusin", "onFocus");
At("focusout", "onBlur");
At(Ea, "onTransitionEnd");
Sn("onMouseEnter", ["mouseout", "mouseover"]);
Sn("onMouseLeave", ["mouseout", "mouseover"]);
Sn("onPointerEnter", ["pointerout", "pointerover"]);
Sn("onPointerLeave", ["pointerout", "pointerover"]);
Zt("onChange", "change click focusin focusout input keydown keyup selectionchange".split(" "));
Zt("onSelect", "focusout contextmenu dragend focusin keydown keyup mousedown mouseup selectionchange".split(" "));
Zt("onBeforeInput", ["compositionend", "keypress", "textInput", "paste"]);
Zt("onCompositionEnd", "compositionend focusout keydown keypress keyup mousedown".split(" "));
Zt("onCompositionStart", "compositionstart focusout keydown keypress keyup mousedown".split(" "));
Zt("onCompositionUpdate", "compositionupdate focusout keydown keypress keyup mousedown".split(" "));
var Gn = "abort canplay canplaythrough durationchange emptied encrypted ended error loadeddata loadedmetadata loadstart pause play playing progress ratechange resize seeked seeking stalled suspend timeupdate volumechange waiting".split(" "), zf = new Set("cancel close invalid load scroll toggle".split(" ").concat(Gn));
function Ru(e, t, n) {
  var r = e.type || "unknown-event";
  e.currentTarget = n, xd(r, t, void 0, e), e.currentTarget = null;
}
function Ca(e, t) {
  t = (t & 4) !== 0;
  for (var n = 0; n < e.length; n++) {
    var r = e[n], l = r.event;
    r = r.listeners;
    e: {
      var i = void 0;
      if (t) for (var o = r.length - 1; 0 <= o; o--) {
        var u = r[o], s = u.instance, c = u.currentTarget;
        if (u = u.listener, s !== i && l.isPropagationStopped()) break e;
        Ru(l, u, c), i = s;
      }
      else for (o = 0; o < r.length; o++) {
        if (u = r[o], s = u.instance, c = u.currentTarget, u = u.listener, s !== i && l.isPropagationStopped()) break e;
        Ru(l, u, c), i = s;
      }
    }
  }
  if (ll) throw e = Fi, ll = !1, Fi = null, e;
}
function Q(e, t) {
  var n = t[Bi];
  n === void 0 && (n = t[Bi] = /* @__PURE__ */ new Set());
  var r = e + "__bubble";
  n.has(r) || (Na(t, e, 2, !1), n.add(r));
}
function li(e, t, n) {
  var r = 0;
  t && (r |= 4), Na(n, e, r, t);
}
var Rr = "_reactListening" + Math.random().toString(36).slice(2);
function dr(e) {
  if (!e[Rr]) {
    e[Rr] = !0, As.forEach(function(n) {
      n !== "selectionchange" && (zf.has(n) || li(n, !1, e), li(n, !0, e));
    });
    var t = e.nodeType === 9 ? e : e.ownerDocument;
    t === null || t[Rr] || (t[Rr] = !0, li("selectionchange", !1, t));
  }
}
function Na(e, t, n, r) {
  switch (ca(t)) {
    case 1:
      var l = Wd;
      break;
    case 4:
      l = Id;
      break;
    default:
      l = Eo;
  }
  n = l.bind(null, t, n, e), l = void 0, !Li || t !== "touchstart" && t !== "touchmove" && t !== "wheel" || (l = !0), r ? l !== void 0 ? e.addEventListener(t, n, { capture: !0, passive: l }) : e.addEventListener(t, n, !0) : l !== void 0 ? e.addEventListener(t, n, { passive: l }) : e.addEventListener(t, n, !1);
}
function ii(e, t, n, r, l) {
  var i = r;
  if (!(t & 1) && !(t & 2) && r !== null) e: for (; ; ) {
    if (r === null) return;
    var o = r.tag;
    if (o === 3 || o === 4) {
      var u = r.stateNode.containerInfo;
      if (u === l || u.nodeType === 8 && u.parentNode === l) break;
      if (o === 4) for (o = r.return; o !== null; ) {
        var s = o.tag;
        if ((s === 3 || s === 4) && (s = o.stateNode.containerInfo, s === l || s.nodeType === 8 && s.parentNode === l)) return;
        o = o.return;
      }
      for (; u !== null; ) {
        if (o = Ut(u), o === null) return;
        if (s = o.tag, s === 5 || s === 6) {
          r = i = o;
          continue e;
        }
        u = u.parentNode;
      }
    }
    r = r.return;
  }
  Zs(function() {
    var c = i, m = wo(n), h = [];
    e: {
      var g = za.get(e);
      if (g !== void 0) {
        var k = Co, x = e;
        switch (e) {
          case "keypress":
            if (Yr(n) === 0) break e;
          case "keydown":
          case "keyup":
            k = ef;
            break;
          case "focusin":
            x = "focus", k = ql;
            break;
          case "focusout":
            x = "blur", k = ql;
            break;
          case "beforeblur":
          case "afterblur":
            k = ql;
            break;
          case "click":
            if (n.button === 2) break e;
          case "auxclick":
          case "dblclick":
          case "mousedown":
          case "mousemove":
          case "mouseup":
          case "mouseout":
          case "mouseover":
          case "contextmenu":
            k = _u;
            break;
          case "drag":
          case "dragend":
          case "dragenter":
          case "dragexit":
          case "dragleave":
          case "dragover":
          case "dragstart":
          case "drop":
            k = Bd;
            break;
          case "touchcancel":
          case "touchend":
          case "touchmove":
          case "touchstart":
            k = rf;
            break;
          case Sa:
          case xa:
          case _a:
            k = Qd;
            break;
          case Ea:
            k = of;
            break;
          case "scroll":
            k = Ud;
            break;
          case "wheel":
            k = sf;
            break;
          case "copy":
          case "cut":
          case "paste":
            k = Gd;
            break;
          case "gotpointercapture":
          case "lostpointercapture":
          case "pointercancel":
          case "pointerdown":
          case "pointermove":
          case "pointerout":
          case "pointerover":
          case "pointerup":
            k = zu;
        }
        var w = (t & 4) !== 0, U = !w && e === "scroll", f = w ? g !== null ? g + "Capture" : null : g;
        w = [];
        for (var a = c, p; a !== null; ) {
          p = a;
          var v = p.stateNode;
          if (p.tag === 5 && v !== null && (p = v, f !== null && (v = ir(a, f), v != null && w.push(fr(a, v, p)))), U) break;
          a = a.return;
        }
        0 < w.length && (g = new k(g, x, null, n, m), h.push({ event: g, listeners: w }));
      }
    }
    if (!(t & 7)) {
      e: {
        if (g = e === "mouseover" || e === "pointerover", k = e === "mouseout" || e === "pointerout", g && n !== Pi && (x = n.relatedTarget || n.fromElement) && (Ut(x) || x[at])) break e;
        if ((k || g) && (g = m.window === m ? m : (g = m.ownerDocument) ? g.defaultView || g.parentWindow : window, k ? (x = n.relatedTarget || n.toElement, k = c, x = x ? Ut(x) : null, x !== null && (U = Jt(x), x !== U || x.tag !== 5 && x.tag !== 6) && (x = null)) : (k = null, x = c), k !== x)) {
          if (w = _u, v = "onMouseLeave", f = "onMouseEnter", a = "mouse", (e === "pointerout" || e === "pointerover") && (w = zu, v = "onPointerLeave", f = "onPointerEnter", a = "pointer"), U = k == null ? g : sn(k), p = x == null ? g : sn(x), g = new w(v, a + "leave", k, n, m), g.target = U, g.relatedTarget = p, v = null, Ut(m) === c && (w = new w(f, a + "enter", x, n, m), w.target = p, w.relatedTarget = U, v = w), U = v, k && x) t: {
            for (w = k, f = x, a = 0, p = w; p; p = en(p)) a++;
            for (p = 0, v = f; v; v = en(v)) p++;
            for (; 0 < a - p; ) w = en(w), a--;
            for (; 0 < p - a; ) f = en(f), p--;
            for (; a--; ) {
              if (w === f || f !== null && w === f.alternate) break t;
              w = en(w), f = en(f);
            }
            w = null;
          }
          else w = null;
          k !== null && Ou(h, g, k, w, !1), x !== null && U !== null && Ou(h, U, x, w, !0);
        }
      }
      e: {
        if (g = c ? sn(c) : window, k = g.nodeName && g.nodeName.toLowerCase(), k === "select" || k === "input" && g.type === "file") var E = hf;
        else if (ju(g)) if (ga) E = kf;
        else {
          E = vf;
          var _ = gf;
        }
        else (k = g.nodeName) && k.toLowerCase() === "input" && (g.type === "checkbox" || g.type === "radio") && (E = yf);
        if (E && (E = E(e, c))) {
          ha(h, E, n, m);
          break e;
        }
        _ && _(e, g, c), e === "focusout" && (_ = g._wrapperState) && _.controlled && g.type === "number" && Ei(g, "number", g.value);
      }
      switch (_ = c ? sn(c) : window, e) {
        case "focusin":
          (ju(_) || _.contentEditable === "true") && (on = _, Oi = c, qn = null);
          break;
        case "focusout":
          qn = Oi = on = null;
          break;
        case "mousedown":
          Di = !0;
          break;
        case "contextmenu":
        case "mouseup":
        case "dragend":
          Di = !1, Mu(h, n, m);
          break;
        case "selectionchange":
          if (xf) break;
        case "keydown":
        case "keyup":
          Mu(h, n, m);
      }
      var N;
      if (jo) e: {
        switch (e) {
          case "compositionstart":
            var j = "onCompositionStart";
            break e;
          case "compositionend":
            j = "onCompositionEnd";
            break e;
          case "compositionupdate":
            j = "onCompositionUpdate";
            break e;
        }
        j = void 0;
      }
      else ln ? pa(e, n) && (j = "onCompositionEnd") : e === "keydown" && n.keyCode === 229 && (j = "onCompositionStart");
      j && (fa && n.locale !== "ko" && (ln || j !== "onCompositionStart" ? j === "onCompositionEnd" && ln && (N = da()) : (xt = m, zo = "value" in xt ? xt.value : xt.textContent, ln = !0)), _ = al(c, j), 0 < _.length && (j = new Eu(j, e, null, n, m), h.push({ event: j, listeners: _ }), N ? j.data = N : (N = ma(n), N !== null && (j.data = N)))), (N = cf ? df(e, n) : ff(e, n)) && (c = al(c, "onBeforeInput"), 0 < c.length && (m = new Eu("onBeforeInput", "beforeinput", null, n, m), h.push({ event: m, listeners: c }), m.data = N));
    }
    Ca(h, t);
  });
}
function fr(e, t, n) {
  return { instance: e, listener: t, currentTarget: n };
}
function al(e, t) {
  for (var n = t + "Capture", r = []; e !== null; ) {
    var l = e, i = l.stateNode;
    l.tag === 5 && i !== null && (l = i, i = ir(e, n), i != null && r.unshift(fr(e, i, l)), i = ir(e, t), i != null && r.push(fr(e, i, l))), e = e.return;
  }
  return r;
}
function en(e) {
  if (e === null) return null;
  do
    e = e.return;
  while (e && e.tag !== 5);
  return e || null;
}
function Ou(e, t, n, r, l) {
  for (var i = t._reactName, o = []; n !== null && n !== r; ) {
    var u = n, s = u.alternate, c = u.stateNode;
    if (s !== null && s === r) break;
    u.tag === 5 && c !== null && (u = c, l ? (s = ir(n, i), s != null && o.unshift(fr(n, s, u))) : l || (s = ir(n, i), s != null && o.push(fr(n, s, u)))), n = n.return;
  }
  o.length !== 0 && e.push({ event: t, listeners: o });
}
var Cf = /\r\n?/g, Nf = /\u0000|\uFFFD/g;
function Du(e) {
  return (typeof e == "string" ? e : "" + e).replace(Cf, `
`).replace(Nf, "");
}
function Or(e, t, n) {
  if (t = Du(t), Du(e) !== t && n) throw Error(y(425));
}
function cl() {
}
var Wi = null, Ii = null;
function Ui(e, t) {
  return e === "textarea" || e === "noscript" || typeof t.children == "string" || typeof t.children == "number" || typeof t.dangerouslySetInnerHTML == "object" && t.dangerouslySetInnerHTML !== null && t.dangerouslySetInnerHTML.__html != null;
}
var $i = typeof setTimeout == "function" ? setTimeout : void 0, jf = typeof clearTimeout == "function" ? clearTimeout : void 0, Wu = typeof Promise == "function" ? Promise : void 0, Pf = typeof queueMicrotask == "function" ? queueMicrotask : typeof Wu < "u" ? function(e) {
  return Wu.resolve(null).then(e).catch(Tf);
} : $i;
function Tf(e) {
  setTimeout(function() {
    throw e;
  });
}
function oi(e, t) {
  var n = t, r = 0;
  do {
    var l = n.nextSibling;
    if (e.removeChild(n), l && l.nodeType === 8) if (n = l.data, n === "/$") {
      if (r === 0) {
        e.removeChild(l), sr(t);
        return;
      }
      r--;
    } else n !== "$" && n !== "$?" && n !== "$!" || r++;
    n = l;
  } while (n);
  sr(t);
}
function Nt(e) {
  for (; e != null; e = e.nextSibling) {
    var t = e.nodeType;
    if (t === 1 || t === 3) break;
    if (t === 8) {
      if (t = e.data, t === "$" || t === "$!" || t === "$?") break;
      if (t === "/$") return null;
    }
  }
  return e;
}
function Iu(e) {
  e = e.previousSibling;
  for (var t = 0; e; ) {
    if (e.nodeType === 8) {
      var n = e.data;
      if (n === "$" || n === "$!" || n === "$?") {
        if (t === 0) return e;
        t--;
      } else n === "/$" && t++;
    }
    e = e.previousSibling;
  }
  return null;
}
var Ln = Math.random().toString(36).slice(2), Xe = "__reactFiber$" + Ln, pr = "__reactProps$" + Ln, at = "__reactContainer$" + Ln, Bi = "__reactEvents$" + Ln, Lf = "__reactListeners$" + Ln, Ff = "__reactHandles$" + Ln;
function Ut(e) {
  var t = e[Xe];
  if (t) return t;
  for (var n = e.parentNode; n; ) {
    if (t = n[at] || n[Xe]) {
      if (n = t.alternate, t.child !== null || n !== null && n.child !== null) for (e = Iu(e); e !== null; ) {
        if (n = e[Xe]) return n;
        e = Iu(e);
      }
      return t;
    }
    e = n, n = e.parentNode;
  }
  return null;
}
function _r(e) {
  return e = e[Xe] || e[at], !e || e.tag !== 5 && e.tag !== 6 && e.tag !== 13 && e.tag !== 3 ? null : e;
}
function sn(e) {
  if (e.tag === 5 || e.tag === 6) return e.stateNode;
  throw Error(y(33));
}
function Ll(e) {
  return e[pr] || null;
}
var Vi = [], an = -1;
function Rt(e) {
  return { current: e };
}
function K(e) {
  0 > an || (e.current = Vi[an], Vi[an] = null, an--);
}
function B(e, t) {
  an++, Vi[an] = e.current, e.current = t;
}
var Mt = {}, he = Rt(Mt), _e = Rt(!1), Qt = Mt;
function xn(e, t) {
  var n = e.type.contextTypes;
  if (!n) return Mt;
  var r = e.stateNode;
  if (r && r.__reactInternalMemoizedUnmaskedChildContext === t) return r.__reactInternalMemoizedMaskedChildContext;
  var l = {}, i;
  for (i in n) l[i] = t[i];
  return r && (e = e.stateNode, e.__reactInternalMemoizedUnmaskedChildContext = t, e.__reactInternalMemoizedMaskedChildContext = l), l;
}
function Ee(e) {
  return e = e.childContextTypes, e != null;
}
function dl() {
  K(_e), K(he);
}
function Uu(e, t, n) {
  if (he.current !== Mt) throw Error(y(168));
  B(he, t), B(_e, n);
}
function ja(e, t, n) {
  var r = e.stateNode;
  if (t = t.childContextTypes, typeof r.getChildContext != "function") return n;
  r = r.getChildContext();
  for (var l in r) if (!(l in t)) throw Error(y(108, hd(e) || "Unknown", l));
  return q({}, n, r);
}
function fl(e) {
  return e = (e = e.stateNode) && e.__reactInternalMemoizedMergedChildContext || Mt, Qt = he.current, B(he, e), B(_e, _e.current), !0;
}
function $u(e, t, n) {
  var r = e.stateNode;
  if (!r) throw Error(y(169));
  n ? (e = ja(e, t, Qt), r.__reactInternalMemoizedMergedChildContext = e, K(_e), K(he), B(he, e)) : K(_e), B(_e, n);
}
var lt = null, Fl = !1, ui = !1;
function Pa(e) {
  lt === null ? lt = [e] : lt.push(e);
}
function Mf(e) {
  Fl = !0, Pa(e);
}
function Ot() {
  if (!ui && lt !== null) {
    ui = !0;
    var e = 0, t = I;
    try {
      var n = lt;
      for (I = 1; e < n.length; e++) {
        var r = n[e];
        do
          r = r(!0);
        while (r !== null);
      }
      lt = null, Fl = !1;
    } catch (l) {
      throw lt !== null && (lt = lt.slice(e + 1)), ea(So, Ot), l;
    } finally {
      I = t, ui = !1;
    }
  }
  return null;
}
var cn = [], dn = 0, pl = null, ml = 0, Fe = [], Me = 0, Kt = null, it = 1, ot = "";
function Wt(e, t) {
  cn[dn++] = ml, cn[dn++] = pl, pl = e, ml = t;
}
function Ta(e, t, n) {
  Fe[Me++] = it, Fe[Me++] = ot, Fe[Me++] = Kt, Kt = e;
  var r = it;
  e = ot;
  var l = 32 - Ve(r) - 1;
  r &= ~(1 << l), n += 1;
  var i = 32 - Ve(t) + l;
  if (30 < i) {
    var o = l - l % 5;
    i = (r & (1 << o) - 1).toString(32), r >>= o, l -= o, it = 1 << 32 - Ve(t) + l | n << l | r, ot = i + e;
  } else it = 1 << i | n << l | r, ot = e;
}
function To(e) {
  e.return !== null && (Wt(e, 1), Ta(e, 1, 0));
}
function Lo(e) {
  for (; e === pl; ) pl = cn[--dn], cn[dn] = null, ml = cn[--dn], cn[dn] = null;
  for (; e === Kt; ) Kt = Fe[--Me], Fe[Me] = null, ot = Fe[--Me], Fe[Me] = null, it = Fe[--Me], Fe[Me] = null;
}
var je = null, Ne = null, G = !1, Be = null;
function La(e, t) {
  var n = Ae(5, null, null, 0);
  n.elementType = "DELETED", n.stateNode = t, n.return = e, t = e.deletions, t === null ? (e.deletions = [n], e.flags |= 16) : t.push(n);
}
function Bu(e, t) {
  switch (e.tag) {
    case 5:
      var n = e.type;
      return t = t.nodeType !== 1 || n.toLowerCase() !== t.nodeName.toLowerCase() ? null : t, t !== null ? (e.stateNode = t, je = e, Ne = Nt(t.firstChild), !0) : !1;
    case 6:
      return t = e.pendingProps === "" || t.nodeType !== 3 ? null : t, t !== null ? (e.stateNode = t, je = e, Ne = null, !0) : !1;
    case 13:
      return t = t.nodeType !== 8 ? null : t, t !== null ? (n = Kt !== null ? { id: it, overflow: ot } : null, e.memoizedState = { dehydrated: t, treeContext: n, retryLane: 1073741824 }, n = Ae(18, null, null, 0), n.stateNode = t, n.return = e, e.child = n, je = e, Ne = null, !0) : !1;
    default:
      return !1;
  }
}
function Hi(e) {
  return (e.mode & 1) !== 0 && (e.flags & 128) === 0;
}
function Qi(e) {
  if (G) {
    var t = Ne;
    if (t) {
      var n = t;
      if (!Bu(e, t)) {
        if (Hi(e)) throw Error(y(418));
        t = Nt(n.nextSibling);
        var r = je;
        t && Bu(e, t) ? La(r, n) : (e.flags = e.flags & -4097 | 2, G = !1, je = e);
      }
    } else {
      if (Hi(e)) throw Error(y(418));
      e.flags = e.flags & -4097 | 2, G = !1, je = e;
    }
  }
}
function Vu(e) {
  for (e = e.return; e !== null && e.tag !== 5 && e.tag !== 3 && e.tag !== 13; ) e = e.return;
  je = e;
}
function Dr(e) {
  if (e !== je) return !1;
  if (!G) return Vu(e), G = !0, !1;
  var t;
  if ((t = e.tag !== 3) && !(t = e.tag !== 5) && (t = e.type, t = t !== "head" && t !== "body" && !Ui(e.type, e.memoizedProps)), t && (t = Ne)) {
    if (Hi(e)) throw Fa(), Error(y(418));
    for (; t; ) La(e, t), t = Nt(t.nextSibling);
  }
  if (Vu(e), e.tag === 13) {
    if (e = e.memoizedState, e = e !== null ? e.dehydrated : null, !e) throw Error(y(317));
    e: {
      for (e = e.nextSibling, t = 0; e; ) {
        if (e.nodeType === 8) {
          var n = e.data;
          if (n === "/$") {
            if (t === 0) {
              Ne = Nt(e.nextSibling);
              break e;
            }
            t--;
          } else n !== "$" && n !== "$!" && n !== "$?" || t++;
        }
        e = e.nextSibling;
      }
      Ne = null;
    }
  } else Ne = je ? Nt(e.stateNode.nextSibling) : null;
  return !0;
}
function Fa() {
  for (var e = Ne; e; ) e = Nt(e.nextSibling);
}
function _n() {
  Ne = je = null, G = !1;
}
function Fo(e) {
  Be === null ? Be = [e] : Be.push(e);
}
var Af = ft.ReactCurrentBatchConfig;
function Un(e, t, n) {
  if (e = n.ref, e !== null && typeof e != "function" && typeof e != "object") {
    if (n._owner) {
      if (n = n._owner, n) {
        if (n.tag !== 1) throw Error(y(309));
        var r = n.stateNode;
      }
      if (!r) throw Error(y(147, e));
      var l = r, i = "" + e;
      return t !== null && t.ref !== null && typeof t.ref == "function" && t.ref._stringRef === i ? t.ref : (t = function(o) {
        var u = l.refs;
        o === null ? delete u[i] : u[i] = o;
      }, t._stringRef = i, t);
    }
    if (typeof e != "string") throw Error(y(284));
    if (!n._owner) throw Error(y(290, e));
  }
  return e;
}
function Wr(e, t) {
  throw e = Object.prototype.toString.call(t), Error(y(31, e === "[object Object]" ? "object with keys {" + Object.keys(t).join(", ") + "}" : e));
}
function Hu(e) {
  var t = e._init;
  return t(e._payload);
}
function Ma(e) {
  function t(f, a) {
    if (e) {
      var p = f.deletions;
      p === null ? (f.deletions = [a], f.flags |= 16) : p.push(a);
    }
  }
  function n(f, a) {
    if (!e) return null;
    for (; a !== null; ) t(f, a), a = a.sibling;
    return null;
  }
  function r(f, a) {
    for (f = /* @__PURE__ */ new Map(); a !== null; ) a.key !== null ? f.set(a.key, a) : f.set(a.index, a), a = a.sibling;
    return f;
  }
  function l(f, a) {
    return f = Lt(f, a), f.index = 0, f.sibling = null, f;
  }
  function i(f, a, p) {
    return f.index = p, e ? (p = f.alternate, p !== null ? (p = p.index, p < a ? (f.flags |= 2, a) : p) : (f.flags |= 2, a)) : (f.flags |= 1048576, a);
  }
  function o(f) {
    return e && f.alternate === null && (f.flags |= 2), f;
  }
  function u(f, a, p, v) {
    return a === null || a.tag !== 6 ? (a = mi(p, f.mode, v), a.return = f, a) : (a = l(a, p), a.return = f, a);
  }
  function s(f, a, p, v) {
    var E = p.type;
    return E === rn ? m(f, a, p.props.children, v, p.key) : a !== null && (a.elementType === E || typeof E == "object" && E !== null && E.$$typeof === yt && Hu(E) === a.type) ? (v = l(a, p.props), v.ref = Un(f, a, p), v.return = f, v) : (v = tl(p.type, p.key, p.props, null, f.mode, v), v.ref = Un(f, a, p), v.return = f, v);
  }
  function c(f, a, p, v) {
    return a === null || a.tag !== 4 || a.stateNode.containerInfo !== p.containerInfo || a.stateNode.implementation !== p.implementation ? (a = hi(p, f.mode, v), a.return = f, a) : (a = l(a, p.children || []), a.return = f, a);
  }
  function m(f, a, p, v, E) {
    return a === null || a.tag !== 7 ? (a = Ht(p, f.mode, v, E), a.return = f, a) : (a = l(a, p), a.return = f, a);
  }
  function h(f, a, p) {
    if (typeof a == "string" && a !== "" || typeof a == "number") return a = mi("" + a, f.mode, p), a.return = f, a;
    if (typeof a == "object" && a !== null) {
      switch (a.$$typeof) {
        case Nr:
          return p = tl(a.type, a.key, a.props, null, f.mode, p), p.ref = Un(f, null, a), p.return = f, p;
        case nn:
          return a = hi(a, f.mode, p), a.return = f, a;
        case yt:
          var v = a._init;
          return h(f, v(a._payload), p);
      }
      if (Qn(a) || Rn(a)) return a = Ht(a, f.mode, p, null), a.return = f, a;
      Wr(f, a);
    }
    return null;
  }
  function g(f, a, p, v) {
    var E = a !== null ? a.key : null;
    if (typeof p == "string" && p !== "" || typeof p == "number") return E !== null ? null : u(f, a, "" + p, v);
    if (typeof p == "object" && p !== null) {
      switch (p.$$typeof) {
        case Nr:
          return p.key === E ? s(f, a, p, v) : null;
        case nn:
          return p.key === E ? c(f, a, p, v) : null;
        case yt:
          return E = p._init, g(
            f,
            a,
            E(p._payload),
            v
          );
      }
      if (Qn(p) || Rn(p)) return E !== null ? null : m(f, a, p, v, null);
      Wr(f, p);
    }
    return null;
  }
  function k(f, a, p, v, E) {
    if (typeof v == "string" && v !== "" || typeof v == "number") return f = f.get(p) || null, u(a, f, "" + v, E);
    if (typeof v == "object" && v !== null) {
      switch (v.$$typeof) {
        case Nr:
          return f = f.get(v.key === null ? p : v.key) || null, s(a, f, v, E);
        case nn:
          return f = f.get(v.key === null ? p : v.key) || null, c(a, f, v, E);
        case yt:
          var _ = v._init;
          return k(f, a, p, _(v._payload), E);
      }
      if (Qn(v) || Rn(v)) return f = f.get(p) || null, m(a, f, v, E, null);
      Wr(a, v);
    }
    return null;
  }
  function x(f, a, p, v) {
    for (var E = null, _ = null, N = a, j = a = 0, W = null; N !== null && j < p.length; j++) {
      N.index > j ? (W = N, N = null) : W = N.sibling;
      var M = g(f, N, p[j], v);
      if (M === null) {
        N === null && (N = W);
        break;
      }
      e && N && M.alternate === null && t(f, N), a = i(M, a, j), _ === null ? E = M : _.sibling = M, _ = M, N = W;
    }
    if (j === p.length) return n(f, N), G && Wt(f, j), E;
    if (N === null) {
      for (; j < p.length; j++) N = h(f, p[j], v), N !== null && (a = i(N, a, j), _ === null ? E = N : _.sibling = N, _ = N);
      return G && Wt(f, j), E;
    }
    for (N = r(f, N); j < p.length; j++) W = k(N, f, j, p[j], v), W !== null && (e && W.alternate !== null && N.delete(W.key === null ? j : W.key), a = i(W, a, j), _ === null ? E = W : _.sibling = W, _ = W);
    return e && N.forEach(function(te) {
      return t(f, te);
    }), G && Wt(f, j), E;
  }
  function w(f, a, p, v) {
    var E = Rn(p);
    if (typeof E != "function") throw Error(y(150));
    if (p = E.call(p), p == null) throw Error(y(151));
    for (var _ = E = null, N = a, j = a = 0, W = null, M = p.next(); N !== null && !M.done; j++, M = p.next()) {
      N.index > j ? (W = N, N = null) : W = N.sibling;
      var te = g(f, N, M.value, v);
      if (te === null) {
        N === null && (N = W);
        break;
      }
      e && N && te.alternate === null && t(f, N), a = i(te, a, j), _ === null ? E = te : _.sibling = te, _ = te, N = W;
    }
    if (M.done) return n(
      f,
      N
    ), G && Wt(f, j), E;
    if (N === null) {
      for (; !M.done; j++, M = p.next()) M = h(f, M.value, v), M !== null && (a = i(M, a, j), _ === null ? E = M : _.sibling = M, _ = M);
      return G && Wt(f, j), E;
    }
    for (N = r(f, N); !M.done; j++, M = p.next()) M = k(N, f, j, M.value, v), M !== null && (e && M.alternate !== null && N.delete(M.key === null ? j : M.key), a = i(M, a, j), _ === null ? E = M : _.sibling = M, _ = M);
    return e && N.forEach(function(ie) {
      return t(f, ie);
    }), G && Wt(f, j), E;
  }
  function U(f, a, p, v) {
    if (typeof p == "object" && p !== null && p.type === rn && p.key === null && (p = p.props.children), typeof p == "object" && p !== null) {
      switch (p.$$typeof) {
        case Nr:
          e: {
            for (var E = p.key, _ = a; _ !== null; ) {
              if (_.key === E) {
                if (E = p.type, E === rn) {
                  if (_.tag === 7) {
                    n(f, _.sibling), a = l(_, p.props.children), a.return = f, f = a;
                    break e;
                  }
                } else if (_.elementType === E || typeof E == "object" && E !== null && E.$$typeof === yt && Hu(E) === _.type) {
                  n(f, _.sibling), a = l(_, p.props), a.ref = Un(f, _, p), a.return = f, f = a;
                  break e;
                }
                n(f, _);
                break;
              } else t(f, _);
              _ = _.sibling;
            }
            p.type === rn ? (a = Ht(p.props.children, f.mode, v, p.key), a.return = f, f = a) : (v = tl(p.type, p.key, p.props, null, f.mode, v), v.ref = Un(f, a, p), v.return = f, f = v);
          }
          return o(f);
        case nn:
          e: {
            for (_ = p.key; a !== null; ) {
              if (a.key === _) if (a.tag === 4 && a.stateNode.containerInfo === p.containerInfo && a.stateNode.implementation === p.implementation) {
                n(f, a.sibling), a = l(a, p.children || []), a.return = f, f = a;
                break e;
              } else {
                n(f, a);
                break;
              }
              else t(f, a);
              a = a.sibling;
            }
            a = hi(p, f.mode, v), a.return = f, f = a;
          }
          return o(f);
        case yt:
          return _ = p._init, U(f, a, _(p._payload), v);
      }
      if (Qn(p)) return x(f, a, p, v);
      if (Rn(p)) return w(f, a, p, v);
      Wr(f, p);
    }
    return typeof p == "string" && p !== "" || typeof p == "number" ? (p = "" + p, a !== null && a.tag === 6 ? (n(f, a.sibling), a = l(a, p), a.return = f, f = a) : (n(f, a), a = mi(p, f.mode, v), a.return = f, f = a), o(f)) : n(f, a);
  }
  return U;
}
var En = Ma(!0), Aa = Ma(!1), hl = Rt(null), gl = null, fn = null, Mo = null;
function Ao() {
  Mo = fn = gl = null;
}
function Ro(e) {
  var t = hl.current;
  K(hl), e._currentValue = t;
}
function Ki(e, t, n) {
  for (; e !== null; ) {
    var r = e.alternate;
    if ((e.childLanes & t) !== t ? (e.childLanes |= t, r !== null && (r.childLanes |= t)) : r !== null && (r.childLanes & t) !== t && (r.childLanes |= t), e === n) break;
    e = e.return;
  }
}
function kn(e, t) {
  gl = e, Mo = fn = null, e = e.dependencies, e !== null && e.firstContext !== null && (e.lanes & t && (xe = !0), e.firstContext = null);
}
function Oe(e) {
  var t = e._currentValue;
  if (Mo !== e) if (e = { context: e, memoizedValue: t, next: null }, fn === null) {
    if (gl === null) throw Error(y(308));
    fn = e, gl.dependencies = { lanes: 0, firstContext: e };
  } else fn = fn.next = e;
  return t;
}
var $t = null;
function Oo(e) {
  $t === null ? $t = [e] : $t.push(e);
}
function Ra(e, t, n, r) {
  var l = t.interleaved;
  return l === null ? (n.next = n, Oo(t)) : (n.next = l.next, l.next = n), t.interleaved = n, ct(e, r);
}
function ct(e, t) {
  e.lanes |= t;
  var n = e.alternate;
  for (n !== null && (n.lanes |= t), n = e, e = e.return; e !== null; ) e.childLanes |= t, n = e.alternate, n !== null && (n.childLanes |= t), n = e, e = e.return;
  return n.tag === 3 ? n.stateNode : null;
}
var kt = !1;
function Do(e) {
  e.updateQueue = { baseState: e.memoizedState, firstBaseUpdate: null, lastBaseUpdate: null, shared: { pending: null, interleaved: null, lanes: 0 }, effects: null };
}
function Oa(e, t) {
  e = e.updateQueue, t.updateQueue === e && (t.updateQueue = { baseState: e.baseState, firstBaseUpdate: e.firstBaseUpdate, lastBaseUpdate: e.lastBaseUpdate, shared: e.shared, effects: e.effects });
}
function ut(e, t) {
  return { eventTime: e, lane: t, tag: 0, payload: null, callback: null, next: null };
}
function jt(e, t, n) {
  var r = e.updateQueue;
  if (r === null) return null;
  if (r = r.shared, D & 2) {
    var l = r.pending;
    return l === null ? t.next = t : (t.next = l.next, l.next = t), r.pending = t, ct(e, n);
  }
  return l = r.interleaved, l === null ? (t.next = t, Oo(r)) : (t.next = l.next, l.next = t), r.interleaved = t, ct(e, n);
}
function Xr(e, t, n) {
  if (t = t.updateQueue, t !== null && (t = t.shared, (n & 4194240) !== 0)) {
    var r = t.lanes;
    r &= e.pendingLanes, n |= r, t.lanes = n, xo(e, n);
  }
}
function Qu(e, t) {
  var n = e.updateQueue, r = e.alternate;
  if (r !== null && (r = r.updateQueue, n === r)) {
    var l = null, i = null;
    if (n = n.firstBaseUpdate, n !== null) {
      do {
        var o = { eventTime: n.eventTime, lane: n.lane, tag: n.tag, payload: n.payload, callback: n.callback, next: null };
        i === null ? l = i = o : i = i.next = o, n = n.next;
      } while (n !== null);
      i === null ? l = i = t : i = i.next = t;
    } else l = i = t;
    n = { baseState: r.baseState, firstBaseUpdate: l, lastBaseUpdate: i, shared: r.shared, effects: r.effects }, e.updateQueue = n;
    return;
  }
  e = n.lastBaseUpdate, e === null ? n.firstBaseUpdate = t : e.next = t, n.lastBaseUpdate = t;
}
function vl(e, t, n, r) {
  var l = e.updateQueue;
  kt = !1;
  var i = l.firstBaseUpdate, o = l.lastBaseUpdate, u = l.shared.pending;
  if (u !== null) {
    l.shared.pending = null;
    var s = u, c = s.next;
    s.next = null, o === null ? i = c : o.next = c, o = s;
    var m = e.alternate;
    m !== null && (m = m.updateQueue, u = m.lastBaseUpdate, u !== o && (u === null ? m.firstBaseUpdate = c : u.next = c, m.lastBaseUpdate = s));
  }
  if (i !== null) {
    var h = l.baseState;
    o = 0, m = c = s = null, u = i;
    do {
      var g = u.lane, k = u.eventTime;
      if ((r & g) === g) {
        m !== null && (m = m.next = {
          eventTime: k,
          lane: 0,
          tag: u.tag,
          payload: u.payload,
          callback: u.callback,
          next: null
        });
        e: {
          var x = e, w = u;
          switch (g = t, k = n, w.tag) {
            case 1:
              if (x = w.payload, typeof x == "function") {
                h = x.call(k, h, g);
                break e;
              }
              h = x;
              break e;
            case 3:
              x.flags = x.flags & -65537 | 128;
            case 0:
              if (x = w.payload, g = typeof x == "function" ? x.call(k, h, g) : x, g == null) break e;
              h = q({}, h, g);
              break e;
            case 2:
              kt = !0;
          }
        }
        u.callback !== null && u.lane !== 0 && (e.flags |= 64, g = l.effects, g === null ? l.effects = [u] : g.push(u));
      } else k = { eventTime: k, lane: g, tag: u.tag, payload: u.payload, callback: u.callback, next: null }, m === null ? (c = m = k, s = h) : m = m.next = k, o |= g;
      if (u = u.next, u === null) {
        if (u = l.shared.pending, u === null) break;
        g = u, u = g.next, g.next = null, l.lastBaseUpdate = g, l.shared.pending = null;
      }
    } while (!0);
    if (m === null && (s = h), l.baseState = s, l.firstBaseUpdate = c, l.lastBaseUpdate = m, t = l.shared.interleaved, t !== null) {
      l = t;
      do
        o |= l.lane, l = l.next;
      while (l !== t);
    } else i === null && (l.shared.lanes = 0);
    Yt |= o, e.lanes = o, e.memoizedState = h;
  }
}
function Ku(e, t, n) {
  if (e = t.effects, t.effects = null, e !== null) for (t = 0; t < e.length; t++) {
    var r = e[t], l = r.callback;
    if (l !== null) {
      if (r.callback = null, r = n, typeof l != "function") throw Error(y(191, l));
      l.call(r);
    }
  }
}
var Er = {}, qe = Rt(Er), mr = Rt(Er), hr = Rt(Er);
function Bt(e) {
  if (e === Er) throw Error(y(174));
  return e;
}
function Wo(e, t) {
  switch (B(hr, t), B(mr, e), B(qe, Er), e = t.nodeType, e) {
    case 9:
    case 11:
      t = (t = t.documentElement) ? t.namespaceURI : Ci(null, "");
      break;
    default:
      e = e === 8 ? t.parentNode : t, t = e.namespaceURI || null, e = e.tagName, t = Ci(t, e);
  }
  K(qe), B(qe, t);
}
function zn() {
  K(qe), K(mr), K(hr);
}
function Da(e) {
  Bt(hr.current);
  var t = Bt(qe.current), n = Ci(t, e.type);
  t !== n && (B(mr, e), B(qe, n));
}
function Io(e) {
  mr.current === e && (K(qe), K(mr));
}
var Z = Rt(0);
function yl(e) {
  for (var t = e; t !== null; ) {
    if (t.tag === 13) {
      var n = t.memoizedState;
      if (n !== null && (n = n.dehydrated, n === null || n.data === "$?" || n.data === "$!")) return t;
    } else if (t.tag === 19 && t.memoizedProps.revealOrder !== void 0) {
      if (t.flags & 128) return t;
    } else if (t.child !== null) {
      t.child.return = t, t = t.child;
      continue;
    }
    if (t === e) break;
    for (; t.sibling === null; ) {
      if (t.return === null || t.return === e) return null;
      t = t.return;
    }
    t.sibling.return = t.return, t = t.sibling;
  }
  return null;
}
var si = [];
function Uo() {
  for (var e = 0; e < si.length; e++) si[e]._workInProgressVersionPrimary = null;
  si.length = 0;
}
var Zr = ft.ReactCurrentDispatcher, ai = ft.ReactCurrentBatchConfig, Gt = 0, J = null, re = null, oe = null, kl = !1, bn = !1, gr = 0, Rf = 0;
function fe() {
  throw Error(y(321));
}
function $o(e, t) {
  if (t === null) return !1;
  for (var n = 0; n < t.length && n < e.length; n++) if (!Qe(e[n], t[n])) return !1;
  return !0;
}
function Bo(e, t, n, r, l, i) {
  if (Gt = i, J = t, t.memoizedState = null, t.updateQueue = null, t.lanes = 0, Zr.current = e === null || e.memoizedState === null ? If : Uf, e = n(r, l), bn) {
    i = 0;
    do {
      if (bn = !1, gr = 0, 25 <= i) throw Error(y(301));
      i += 1, oe = re = null, t.updateQueue = null, Zr.current = $f, e = n(r, l);
    } while (bn);
  }
  if (Zr.current = wl, t = re !== null && re.next !== null, Gt = 0, oe = re = J = null, kl = !1, t) throw Error(y(300));
  return e;
}
function Vo() {
  var e = gr !== 0;
  return gr = 0, e;
}
function Ye() {
  var e = { memoizedState: null, baseState: null, baseQueue: null, queue: null, next: null };
  return oe === null ? J.memoizedState = oe = e : oe = oe.next = e, oe;
}
function De() {
  if (re === null) {
    var e = J.alternate;
    e = e !== null ? e.memoizedState : null;
  } else e = re.next;
  var t = oe === null ? J.memoizedState : oe.next;
  if (t !== null) oe = t, re = e;
  else {
    if (e === null) throw Error(y(310));
    re = e, e = { memoizedState: re.memoizedState, baseState: re.baseState, baseQueue: re.baseQueue, queue: re.queue, next: null }, oe === null ? J.memoizedState = oe = e : oe = oe.next = e;
  }
  return oe;
}
function vr(e, t) {
  return typeof t == "function" ? t(e) : t;
}
function ci(e) {
  var t = De(), n = t.queue;
  if (n === null) throw Error(y(311));
  n.lastRenderedReducer = e;
  var r = re, l = r.baseQueue, i = n.pending;
  if (i !== null) {
    if (l !== null) {
      var o = l.next;
      l.next = i.next, i.next = o;
    }
    r.baseQueue = l = i, n.pending = null;
  }
  if (l !== null) {
    i = l.next, r = r.baseState;
    var u = o = null, s = null, c = i;
    do {
      var m = c.lane;
      if ((Gt & m) === m) s !== null && (s = s.next = { lane: 0, action: c.action, hasEagerState: c.hasEagerState, eagerState: c.eagerState, next: null }), r = c.hasEagerState ? c.eagerState : e(r, c.action);
      else {
        var h = {
          lane: m,
          action: c.action,
          hasEagerState: c.hasEagerState,
          eagerState: c.eagerState,
          next: null
        };
        s === null ? (u = s = h, o = r) : s = s.next = h, J.lanes |= m, Yt |= m;
      }
      c = c.next;
    } while (c !== null && c !== i);
    s === null ? o = r : s.next = u, Qe(r, t.memoizedState) || (xe = !0), t.memoizedState = r, t.baseState = o, t.baseQueue = s, n.lastRenderedState = r;
  }
  if (e = n.interleaved, e !== null) {
    l = e;
    do
      i = l.lane, J.lanes |= i, Yt |= i, l = l.next;
    while (l !== e);
  } else l === null && (n.lanes = 0);
  return [t.memoizedState, n.dispatch];
}
function di(e) {
  var t = De(), n = t.queue;
  if (n === null) throw Error(y(311));
  n.lastRenderedReducer = e;
  var r = n.dispatch, l = n.pending, i = t.memoizedState;
  if (l !== null) {
    n.pending = null;
    var o = l = l.next;
    do
      i = e(i, o.action), o = o.next;
    while (o !== l);
    Qe(i, t.memoizedState) || (xe = !0), t.memoizedState = i, t.baseQueue === null && (t.baseState = i), n.lastRenderedState = i;
  }
  return [i, r];
}
function Wa() {
}
function Ia(e, t) {
  var n = J, r = De(), l = t(), i = !Qe(r.memoizedState, l);
  if (i && (r.memoizedState = l, xe = !0), r = r.queue, Ho(Ba.bind(null, n, r, e), [e]), r.getSnapshot !== t || i || oe !== null && oe.memoizedState.tag & 1) {
    if (n.flags |= 2048, yr(9, $a.bind(null, n, r, l, t), void 0, null), ue === null) throw Error(y(349));
    Gt & 30 || Ua(n, t, l);
  }
  return l;
}
function Ua(e, t, n) {
  e.flags |= 16384, e = { getSnapshot: t, value: n }, t = J.updateQueue, t === null ? (t = { lastEffect: null, stores: null }, J.updateQueue = t, t.stores = [e]) : (n = t.stores, n === null ? t.stores = [e] : n.push(e));
}
function $a(e, t, n, r) {
  t.value = n, t.getSnapshot = r, Va(t) && Ha(e);
}
function Ba(e, t, n) {
  return n(function() {
    Va(t) && Ha(e);
  });
}
function Va(e) {
  var t = e.getSnapshot;
  e = e.value;
  try {
    var n = t();
    return !Qe(e, n);
  } catch {
    return !0;
  }
}
function Ha(e) {
  var t = ct(e, 1);
  t !== null && He(t, e, 1, -1);
}
function Gu(e) {
  var t = Ye();
  return typeof e == "function" && (e = e()), t.memoizedState = t.baseState = e, e = { pending: null, interleaved: null, lanes: 0, dispatch: null, lastRenderedReducer: vr, lastRenderedState: e }, t.queue = e, e = e.dispatch = Wf.bind(null, J, e), [t.memoizedState, e];
}
function yr(e, t, n, r) {
  return e = { tag: e, create: t, destroy: n, deps: r, next: null }, t = J.updateQueue, t === null ? (t = { lastEffect: null, stores: null }, J.updateQueue = t, t.lastEffect = e.next = e) : (n = t.lastEffect, n === null ? t.lastEffect = e.next = e : (r = n.next, n.next = e, e.next = r, t.lastEffect = e)), e;
}
function Qa() {
  return De().memoizedState;
}
function Jr(e, t, n, r) {
  var l = Ye();
  J.flags |= e, l.memoizedState = yr(1 | t, n, void 0, r === void 0 ? null : r);
}
function Ml(e, t, n, r) {
  var l = De();
  r = r === void 0 ? null : r;
  var i = void 0;
  if (re !== null) {
    var o = re.memoizedState;
    if (i = o.destroy, r !== null && $o(r, o.deps)) {
      l.memoizedState = yr(t, n, i, r);
      return;
    }
  }
  J.flags |= e, l.memoizedState = yr(1 | t, n, i, r);
}
function Yu(e, t) {
  return Jr(8390656, 8, e, t);
}
function Ho(e, t) {
  return Ml(2048, 8, e, t);
}
function Ka(e, t) {
  return Ml(4, 2, e, t);
}
function Ga(e, t) {
  return Ml(4, 4, e, t);
}
function Ya(e, t) {
  if (typeof t == "function") return e = e(), t(e), function() {
    t(null);
  };
  if (t != null) return e = e(), t.current = e, function() {
    t.current = null;
  };
}
function Xa(e, t, n) {
  return n = n != null ? n.concat([e]) : null, Ml(4, 4, Ya.bind(null, t, e), n);
}
function Qo() {
}
function Za(e, t) {
  var n = De();
  t = t === void 0 ? null : t;
  var r = n.memoizedState;
  return r !== null && t !== null && $o(t, r[1]) ? r[0] : (n.memoizedState = [e, t], e);
}
function Ja(e, t) {
  var n = De();
  t = t === void 0 ? null : t;
  var r = n.memoizedState;
  return r !== null && t !== null && $o(t, r[1]) ? r[0] : (e = e(), n.memoizedState = [e, t], e);
}
function qa(e, t, n) {
  return Gt & 21 ? (Qe(n, t) || (n = ra(), J.lanes |= n, Yt |= n, e.baseState = !0), t) : (e.baseState && (e.baseState = !1, xe = !0), e.memoizedState = n);
}
function Of(e, t) {
  var n = I;
  I = n !== 0 && 4 > n ? n : 4, e(!0);
  var r = ai.transition;
  ai.transition = {};
  try {
    e(!1), t();
  } finally {
    I = n, ai.transition = r;
  }
}
function ba() {
  return De().memoizedState;
}
function Df(e, t, n) {
  var r = Tt(e);
  if (n = { lane: r, action: n, hasEagerState: !1, eagerState: null, next: null }, ec(e)) tc(t, n);
  else if (n = Ra(e, t, n, r), n !== null) {
    var l = ye();
    He(n, e, r, l), nc(n, t, r);
  }
}
function Wf(e, t, n) {
  var r = Tt(e), l = { lane: r, action: n, hasEagerState: !1, eagerState: null, next: null };
  if (ec(e)) tc(t, l);
  else {
    var i = e.alternate;
    if (e.lanes === 0 && (i === null || i.lanes === 0) && (i = t.lastRenderedReducer, i !== null)) try {
      var o = t.lastRenderedState, u = i(o, n);
      if (l.hasEagerState = !0, l.eagerState = u, Qe(u, o)) {
        var s = t.interleaved;
        s === null ? (l.next = l, Oo(t)) : (l.next = s.next, s.next = l), t.interleaved = l;
        return;
      }
    } catch {
    } finally {
    }
    n = Ra(e, t, l, r), n !== null && (l = ye(), He(n, e, r, l), nc(n, t, r));
  }
}
function ec(e) {
  var t = e.alternate;
  return e === J || t !== null && t === J;
}
function tc(e, t) {
  bn = kl = !0;
  var n = e.pending;
  n === null ? t.next = t : (t.next = n.next, n.next = t), e.pending = t;
}
function nc(e, t, n) {
  if (n & 4194240) {
    var r = t.lanes;
    r &= e.pendingLanes, n |= r, t.lanes = n, xo(e, n);
  }
}
var wl = { readContext: Oe, useCallback: fe, useContext: fe, useEffect: fe, useImperativeHandle: fe, useInsertionEffect: fe, useLayoutEffect: fe, useMemo: fe, useReducer: fe, useRef: fe, useState: fe, useDebugValue: fe, useDeferredValue: fe, useTransition: fe, useMutableSource: fe, useSyncExternalStore: fe, useId: fe, unstable_isNewReconciler: !1 }, If = { readContext: Oe, useCallback: function(e, t) {
  return Ye().memoizedState = [e, t === void 0 ? null : t], e;
}, useContext: Oe, useEffect: Yu, useImperativeHandle: function(e, t, n) {
  return n = n != null ? n.concat([e]) : null, Jr(
    4194308,
    4,
    Ya.bind(null, t, e),
    n
  );
}, useLayoutEffect: function(e, t) {
  return Jr(4194308, 4, e, t);
}, useInsertionEffect: function(e, t) {
  return Jr(4, 2, e, t);
}, useMemo: function(e, t) {
  var n = Ye();
  return t = t === void 0 ? null : t, e = e(), n.memoizedState = [e, t], e;
}, useReducer: function(e, t, n) {
  var r = Ye();
  return t = n !== void 0 ? n(t) : t, r.memoizedState = r.baseState = t, e = { pending: null, interleaved: null, lanes: 0, dispatch: null, lastRenderedReducer: e, lastRenderedState: t }, r.queue = e, e = e.dispatch = Df.bind(null, J, e), [r.memoizedState, e];
}, useRef: function(e) {
  var t = Ye();
  return e = { current: e }, t.memoizedState = e;
}, useState: Gu, useDebugValue: Qo, useDeferredValue: function(e) {
  return Ye().memoizedState = e;
}, useTransition: function() {
  var e = Gu(!1), t = e[0];
  return e = Of.bind(null, e[1]), Ye().memoizedState = e, [t, e];
}, useMutableSource: function() {
}, useSyncExternalStore: function(e, t, n) {
  var r = J, l = Ye();
  if (G) {
    if (n === void 0) throw Error(y(407));
    n = n();
  } else {
    if (n = t(), ue === null) throw Error(y(349));
    Gt & 30 || Ua(r, t, n);
  }
  l.memoizedState = n;
  var i = { value: n, getSnapshot: t };
  return l.queue = i, Yu(Ba.bind(
    null,
    r,
    i,
    e
  ), [e]), r.flags |= 2048, yr(9, $a.bind(null, r, i, n, t), void 0, null), n;
}, useId: function() {
  var e = Ye(), t = ue.identifierPrefix;
  if (G) {
    var n = ot, r = it;
    n = (r & ~(1 << 32 - Ve(r) - 1)).toString(32) + n, t = ":" + t + "R" + n, n = gr++, 0 < n && (t += "H" + n.toString(32)), t += ":";
  } else n = Rf++, t = ":" + t + "r" + n.toString(32) + ":";
  return e.memoizedState = t;
}, unstable_isNewReconciler: !1 }, Uf = {
  readContext: Oe,
  useCallback: Za,
  useContext: Oe,
  useEffect: Ho,
  useImperativeHandle: Xa,
  useInsertionEffect: Ka,
  useLayoutEffect: Ga,
  useMemo: Ja,
  useReducer: ci,
  useRef: Qa,
  useState: function() {
    return ci(vr);
  },
  useDebugValue: Qo,
  useDeferredValue: function(e) {
    var t = De();
    return qa(t, re.memoizedState, e);
  },
  useTransition: function() {
    var e = ci(vr)[0], t = De().memoizedState;
    return [e, t];
  },
  useMutableSource: Wa,
  useSyncExternalStore: Ia,
  useId: ba,
  unstable_isNewReconciler: !1
}, $f = { readContext: Oe, useCallback: Za, useContext: Oe, useEffect: Ho, useImperativeHandle: Xa, useInsertionEffect: Ka, useLayoutEffect: Ga, useMemo: Ja, useReducer: di, useRef: Qa, useState: function() {
  return di(vr);
}, useDebugValue: Qo, useDeferredValue: function(e) {
  var t = De();
  return re === null ? t.memoizedState = e : qa(t, re.memoizedState, e);
}, useTransition: function() {
  var e = di(vr)[0], t = De().memoizedState;
  return [e, t];
}, useMutableSource: Wa, useSyncExternalStore: Ia, useId: ba, unstable_isNewReconciler: !1 };
function Ue(e, t) {
  if (e && e.defaultProps) {
    t = q({}, t), e = e.defaultProps;
    for (var n in e) t[n] === void 0 && (t[n] = e[n]);
    return t;
  }
  return t;
}
function Gi(e, t, n, r) {
  t = e.memoizedState, n = n(r, t), n = n == null ? t : q({}, t, n), e.memoizedState = n, e.lanes === 0 && (e.updateQueue.baseState = n);
}
var Al = { isMounted: function(e) {
  return (e = e._reactInternals) ? Jt(e) === e : !1;
}, enqueueSetState: function(e, t, n) {
  e = e._reactInternals;
  var r = ye(), l = Tt(e), i = ut(r, l);
  i.payload = t, n != null && (i.callback = n), t = jt(e, i, l), t !== null && (He(t, e, l, r), Xr(t, e, l));
}, enqueueReplaceState: function(e, t, n) {
  e = e._reactInternals;
  var r = ye(), l = Tt(e), i = ut(r, l);
  i.tag = 1, i.payload = t, n != null && (i.callback = n), t = jt(e, i, l), t !== null && (He(t, e, l, r), Xr(t, e, l));
}, enqueueForceUpdate: function(e, t) {
  e = e._reactInternals;
  var n = ye(), r = Tt(e), l = ut(n, r);
  l.tag = 2, t != null && (l.callback = t), t = jt(e, l, r), t !== null && (He(t, e, r, n), Xr(t, e, r));
} };
function Xu(e, t, n, r, l, i, o) {
  return e = e.stateNode, typeof e.shouldComponentUpdate == "function" ? e.shouldComponentUpdate(r, i, o) : t.prototype && t.prototype.isPureReactComponent ? !cr(n, r) || !cr(l, i) : !0;
}
function rc(e, t, n) {
  var r = !1, l = Mt, i = t.contextType;
  return typeof i == "object" && i !== null ? i = Oe(i) : (l = Ee(t) ? Qt : he.current, r = t.contextTypes, i = (r = r != null) ? xn(e, l) : Mt), t = new t(n, i), e.memoizedState = t.state !== null && t.state !== void 0 ? t.state : null, t.updater = Al, e.stateNode = t, t._reactInternals = e, r && (e = e.stateNode, e.__reactInternalMemoizedUnmaskedChildContext = l, e.__reactInternalMemoizedMaskedChildContext = i), t;
}
function Zu(e, t, n, r) {
  e = t.state, typeof t.componentWillReceiveProps == "function" && t.componentWillReceiveProps(n, r), typeof t.UNSAFE_componentWillReceiveProps == "function" && t.UNSAFE_componentWillReceiveProps(n, r), t.state !== e && Al.enqueueReplaceState(t, t.state, null);
}
function Yi(e, t, n, r) {
  var l = e.stateNode;
  l.props = n, l.state = e.memoizedState, l.refs = {}, Do(e);
  var i = t.contextType;
  typeof i == "object" && i !== null ? l.context = Oe(i) : (i = Ee(t) ? Qt : he.current, l.context = xn(e, i)), l.state = e.memoizedState, i = t.getDerivedStateFromProps, typeof i == "function" && (Gi(e, t, i, n), l.state = e.memoizedState), typeof t.getDerivedStateFromProps == "function" || typeof l.getSnapshotBeforeUpdate == "function" || typeof l.UNSAFE_componentWillMount != "function" && typeof l.componentWillMount != "function" || (t = l.state, typeof l.componentWillMount == "function" && l.componentWillMount(), typeof l.UNSAFE_componentWillMount == "function" && l.UNSAFE_componentWillMount(), t !== l.state && Al.enqueueReplaceState(l, l.state, null), vl(e, n, l, r), l.state = e.memoizedState), typeof l.componentDidMount == "function" && (e.flags |= 4194308);
}
function Cn(e, t) {
  try {
    var n = "", r = t;
    do
      n += md(r), r = r.return;
    while (r);
    var l = n;
  } catch (i) {
    l = `
Error generating stack: ` + i.message + `
` + i.stack;
  }
  return { value: e, source: t, stack: l, digest: null };
}
function fi(e, t, n) {
  return { value: e, source: null, stack: n ?? null, digest: t ?? null };
}
function Xi(e, t) {
  try {
    console.error(t.value);
  } catch (n) {
    setTimeout(function() {
      throw n;
    });
  }
}
var Bf = typeof WeakMap == "function" ? WeakMap : Map;
function lc(e, t, n) {
  n = ut(-1, n), n.tag = 3, n.payload = { element: null };
  var r = t.value;
  return n.callback = function() {
    xl || (xl = !0, io = r), Xi(e, t);
  }, n;
}
function ic(e, t, n) {
  n = ut(-1, n), n.tag = 3;
  var r = e.type.getDerivedStateFromError;
  if (typeof r == "function") {
    var l = t.value;
    n.payload = function() {
      return r(l);
    }, n.callback = function() {
      Xi(e, t);
    };
  }
  var i = e.stateNode;
  return i !== null && typeof i.componentDidCatch == "function" && (n.callback = function() {
    Xi(e, t), typeof r != "function" && (Pt === null ? Pt = /* @__PURE__ */ new Set([this]) : Pt.add(this));
    var o = t.stack;
    this.componentDidCatch(t.value, { componentStack: o !== null ? o : "" });
  }), n;
}
function Ju(e, t, n) {
  var r = e.pingCache;
  if (r === null) {
    r = e.pingCache = new Bf();
    var l = /* @__PURE__ */ new Set();
    r.set(t, l);
  } else l = r.get(t), l === void 0 && (l = /* @__PURE__ */ new Set(), r.set(t, l));
  l.has(n) || (l.add(n), e = np.bind(null, e, t, n), t.then(e, e));
}
function qu(e) {
  do {
    var t;
    if ((t = e.tag === 13) && (t = e.memoizedState, t = t !== null ? t.dehydrated !== null : !0), t) return e;
    e = e.return;
  } while (e !== null);
  return null;
}
function bu(e, t, n, r, l) {
  return e.mode & 1 ? (e.flags |= 65536, e.lanes = l, e) : (e === t ? e.flags |= 65536 : (e.flags |= 128, n.flags |= 131072, n.flags &= -52805, n.tag === 1 && (n.alternate === null ? n.tag = 17 : (t = ut(-1, 1), t.tag = 2, jt(n, t, 1))), n.lanes |= 1), e);
}
var Vf = ft.ReactCurrentOwner, xe = !1;
function ve(e, t, n, r) {
  t.child = e === null ? Aa(t, null, n, r) : En(t, e.child, n, r);
}
function es(e, t, n, r, l) {
  n = n.render;
  var i = t.ref;
  return kn(t, l), r = Bo(e, t, n, r, i, l), n = Vo(), e !== null && !xe ? (t.updateQueue = e.updateQueue, t.flags &= -2053, e.lanes &= ~l, dt(e, t, l)) : (G && n && To(t), t.flags |= 1, ve(e, t, r, l), t.child);
}
function ts(e, t, n, r, l) {
  if (e === null) {
    var i = n.type;
    return typeof i == "function" && !bo(i) && i.defaultProps === void 0 && n.compare === null && n.defaultProps === void 0 ? (t.tag = 15, t.type = i, oc(e, t, i, r, l)) : (e = tl(n.type, null, r, t, t.mode, l), e.ref = t.ref, e.return = t, t.child = e);
  }
  if (i = e.child, !(e.lanes & l)) {
    var o = i.memoizedProps;
    if (n = n.compare, n = n !== null ? n : cr, n(o, r) && e.ref === t.ref) return dt(e, t, l);
  }
  return t.flags |= 1, e = Lt(i, r), e.ref = t.ref, e.return = t, t.child = e;
}
function oc(e, t, n, r, l) {
  if (e !== null) {
    var i = e.memoizedProps;
    if (cr(i, r) && e.ref === t.ref) if (xe = !1, t.pendingProps = r = i, (e.lanes & l) !== 0) e.flags & 131072 && (xe = !0);
    else return t.lanes = e.lanes, dt(e, t, l);
  }
  return Zi(e, t, n, r, l);
}
function uc(e, t, n) {
  var r = t.pendingProps, l = r.children, i = e !== null ? e.memoizedState : null;
  if (r.mode === "hidden") if (!(t.mode & 1)) t.memoizedState = { baseLanes: 0, cachePool: null, transitions: null }, B(mn, Ce), Ce |= n;
  else {
    if (!(n & 1073741824)) return e = i !== null ? i.baseLanes | n : n, t.lanes = t.childLanes = 1073741824, t.memoizedState = { baseLanes: e, cachePool: null, transitions: null }, t.updateQueue = null, B(mn, Ce), Ce |= e, null;
    t.memoizedState = { baseLanes: 0, cachePool: null, transitions: null }, r = i !== null ? i.baseLanes : n, B(mn, Ce), Ce |= r;
  }
  else i !== null ? (r = i.baseLanes | n, t.memoizedState = null) : r = n, B(mn, Ce), Ce |= r;
  return ve(e, t, l, n), t.child;
}
function sc(e, t) {
  var n = t.ref;
  (e === null && n !== null || e !== null && e.ref !== n) && (t.flags |= 512, t.flags |= 2097152);
}
function Zi(e, t, n, r, l) {
  var i = Ee(n) ? Qt : he.current;
  return i = xn(t, i), kn(t, l), n = Bo(e, t, n, r, i, l), r = Vo(), e !== null && !xe ? (t.updateQueue = e.updateQueue, t.flags &= -2053, e.lanes &= ~l, dt(e, t, l)) : (G && r && To(t), t.flags |= 1, ve(e, t, n, l), t.child);
}
function ns(e, t, n, r, l) {
  if (Ee(n)) {
    var i = !0;
    fl(t);
  } else i = !1;
  if (kn(t, l), t.stateNode === null) qr(e, t), rc(t, n, r), Yi(t, n, r, l), r = !0;
  else if (e === null) {
    var o = t.stateNode, u = t.memoizedProps;
    o.props = u;
    var s = o.context, c = n.contextType;
    typeof c == "object" && c !== null ? c = Oe(c) : (c = Ee(n) ? Qt : he.current, c = xn(t, c));
    var m = n.getDerivedStateFromProps, h = typeof m == "function" || typeof o.getSnapshotBeforeUpdate == "function";
    h || typeof o.UNSAFE_componentWillReceiveProps != "function" && typeof o.componentWillReceiveProps != "function" || (u !== r || s !== c) && Zu(t, o, r, c), kt = !1;
    var g = t.memoizedState;
    o.state = g, vl(t, r, o, l), s = t.memoizedState, u !== r || g !== s || _e.current || kt ? (typeof m == "function" && (Gi(t, n, m, r), s = t.memoizedState), (u = kt || Xu(t, n, u, r, g, s, c)) ? (h || typeof o.UNSAFE_componentWillMount != "function" && typeof o.componentWillMount != "function" || (typeof o.componentWillMount == "function" && o.componentWillMount(), typeof o.UNSAFE_componentWillMount == "function" && o.UNSAFE_componentWillMount()), typeof o.componentDidMount == "function" && (t.flags |= 4194308)) : (typeof o.componentDidMount == "function" && (t.flags |= 4194308), t.memoizedProps = r, t.memoizedState = s), o.props = r, o.state = s, o.context = c, r = u) : (typeof o.componentDidMount == "function" && (t.flags |= 4194308), r = !1);
  } else {
    o = t.stateNode, Oa(e, t), u = t.memoizedProps, c = t.type === t.elementType ? u : Ue(t.type, u), o.props = c, h = t.pendingProps, g = o.context, s = n.contextType, typeof s == "object" && s !== null ? s = Oe(s) : (s = Ee(n) ? Qt : he.current, s = xn(t, s));
    var k = n.getDerivedStateFromProps;
    (m = typeof k == "function" || typeof o.getSnapshotBeforeUpdate == "function") || typeof o.UNSAFE_componentWillReceiveProps != "function" && typeof o.componentWillReceiveProps != "function" || (u !== h || g !== s) && Zu(t, o, r, s), kt = !1, g = t.memoizedState, o.state = g, vl(t, r, o, l);
    var x = t.memoizedState;
    u !== h || g !== x || _e.current || kt ? (typeof k == "function" && (Gi(t, n, k, r), x = t.memoizedState), (c = kt || Xu(t, n, c, r, g, x, s) || !1) ? (m || typeof o.UNSAFE_componentWillUpdate != "function" && typeof o.componentWillUpdate != "function" || (typeof o.componentWillUpdate == "function" && o.componentWillUpdate(r, x, s), typeof o.UNSAFE_componentWillUpdate == "function" && o.UNSAFE_componentWillUpdate(r, x, s)), typeof o.componentDidUpdate == "function" && (t.flags |= 4), typeof o.getSnapshotBeforeUpdate == "function" && (t.flags |= 1024)) : (typeof o.componentDidUpdate != "function" || u === e.memoizedProps && g === e.memoizedState || (t.flags |= 4), typeof o.getSnapshotBeforeUpdate != "function" || u === e.memoizedProps && g === e.memoizedState || (t.flags |= 1024), t.memoizedProps = r, t.memoizedState = x), o.props = r, o.state = x, o.context = s, r = c) : (typeof o.componentDidUpdate != "function" || u === e.memoizedProps && g === e.memoizedState || (t.flags |= 4), typeof o.getSnapshotBeforeUpdate != "function" || u === e.memoizedProps && g === e.memoizedState || (t.flags |= 1024), r = !1);
  }
  return Ji(e, t, n, r, i, l);
}
function Ji(e, t, n, r, l, i) {
  sc(e, t);
  var o = (t.flags & 128) !== 0;
  if (!r && !o) return l && $u(t, n, !1), dt(e, t, i);
  r = t.stateNode, Vf.current = t;
  var u = o && typeof n.getDerivedStateFromError != "function" ? null : r.render();
  return t.flags |= 1, e !== null && o ? (t.child = En(t, e.child, null, i), t.child = En(t, null, u, i)) : ve(e, t, u, i), t.memoizedState = r.state, l && $u(t, n, !0), t.child;
}
function ac(e) {
  var t = e.stateNode;
  t.pendingContext ? Uu(e, t.pendingContext, t.pendingContext !== t.context) : t.context && Uu(e, t.context, !1), Wo(e, t.containerInfo);
}
function rs(e, t, n, r, l) {
  return _n(), Fo(l), t.flags |= 256, ve(e, t, n, r), t.child;
}
var qi = { dehydrated: null, treeContext: null, retryLane: 0 };
function bi(e) {
  return { baseLanes: e, cachePool: null, transitions: null };
}
function cc(e, t, n) {
  var r = t.pendingProps, l = Z.current, i = !1, o = (t.flags & 128) !== 0, u;
  if ((u = o) || (u = e !== null && e.memoizedState === null ? !1 : (l & 2) !== 0), u ? (i = !0, t.flags &= -129) : (e === null || e.memoizedState !== null) && (l |= 1), B(Z, l & 1), e === null)
    return Qi(t), e = t.memoizedState, e !== null && (e = e.dehydrated, e !== null) ? (t.mode & 1 ? e.data === "$!" ? t.lanes = 8 : t.lanes = 1073741824 : t.lanes = 1, null) : (o = r.children, e = r.fallback, i ? (r = t.mode, i = t.child, o = { mode: "hidden", children: o }, !(r & 1) && i !== null ? (i.childLanes = 0, i.pendingProps = o) : i = Dl(o, r, 0, null), e = Ht(e, r, n, null), i.return = t, e.return = t, i.sibling = e, t.child = i, t.child.memoizedState = bi(n), t.memoizedState = qi, e) : Ko(t, o));
  if (l = e.memoizedState, l !== null && (u = l.dehydrated, u !== null)) return Hf(e, t, o, r, u, l, n);
  if (i) {
    i = r.fallback, o = t.mode, l = e.child, u = l.sibling;
    var s = { mode: "hidden", children: r.children };
    return !(o & 1) && t.child !== l ? (r = t.child, r.childLanes = 0, r.pendingProps = s, t.deletions = null) : (r = Lt(l, s), r.subtreeFlags = l.subtreeFlags & 14680064), u !== null ? i = Lt(u, i) : (i = Ht(i, o, n, null), i.flags |= 2), i.return = t, r.return = t, r.sibling = i, t.child = r, r = i, i = t.child, o = e.child.memoizedState, o = o === null ? bi(n) : { baseLanes: o.baseLanes | n, cachePool: null, transitions: o.transitions }, i.memoizedState = o, i.childLanes = e.childLanes & ~n, t.memoizedState = qi, r;
  }
  return i = e.child, e = i.sibling, r = Lt(i, { mode: "visible", children: r.children }), !(t.mode & 1) && (r.lanes = n), r.return = t, r.sibling = null, e !== null && (n = t.deletions, n === null ? (t.deletions = [e], t.flags |= 16) : n.push(e)), t.child = r, t.memoizedState = null, r;
}
function Ko(e, t) {
  return t = Dl({ mode: "visible", children: t }, e.mode, 0, null), t.return = e, e.child = t;
}
function Ir(e, t, n, r) {
  return r !== null && Fo(r), En(t, e.child, null, n), e = Ko(t, t.pendingProps.children), e.flags |= 2, t.memoizedState = null, e;
}
function Hf(e, t, n, r, l, i, o) {
  if (n)
    return t.flags & 256 ? (t.flags &= -257, r = fi(Error(y(422))), Ir(e, t, o, r)) : t.memoizedState !== null ? (t.child = e.child, t.flags |= 128, null) : (i = r.fallback, l = t.mode, r = Dl({ mode: "visible", children: r.children }, l, 0, null), i = Ht(i, l, o, null), i.flags |= 2, r.return = t, i.return = t, r.sibling = i, t.child = r, t.mode & 1 && En(t, e.child, null, o), t.child.memoizedState = bi(o), t.memoizedState = qi, i);
  if (!(t.mode & 1)) return Ir(e, t, o, null);
  if (l.data === "$!") {
    if (r = l.nextSibling && l.nextSibling.dataset, r) var u = r.dgst;
    return r = u, i = Error(y(419)), r = fi(i, r, void 0), Ir(e, t, o, r);
  }
  if (u = (o & e.childLanes) !== 0, xe || u) {
    if (r = ue, r !== null) {
      switch (o & -o) {
        case 4:
          l = 2;
          break;
        case 16:
          l = 8;
          break;
        case 64:
        case 128:
        case 256:
        case 512:
        case 1024:
        case 2048:
        case 4096:
        case 8192:
        case 16384:
        case 32768:
        case 65536:
        case 131072:
        case 262144:
        case 524288:
        case 1048576:
        case 2097152:
        case 4194304:
        case 8388608:
        case 16777216:
        case 33554432:
        case 67108864:
          l = 32;
          break;
        case 536870912:
          l = 268435456;
          break;
        default:
          l = 0;
      }
      l = l & (r.suspendedLanes | o) ? 0 : l, l !== 0 && l !== i.retryLane && (i.retryLane = l, ct(e, l), He(r, e, l, -1));
    }
    return qo(), r = fi(Error(y(421))), Ir(e, t, o, r);
  }
  return l.data === "$?" ? (t.flags |= 128, t.child = e.child, t = rp.bind(null, e), l._reactRetry = t, null) : (e = i.treeContext, Ne = Nt(l.nextSibling), je = t, G = !0, Be = null, e !== null && (Fe[Me++] = it, Fe[Me++] = ot, Fe[Me++] = Kt, it = e.id, ot = e.overflow, Kt = t), t = Ko(t, r.children), t.flags |= 4096, t);
}
function ls(e, t, n) {
  e.lanes |= t;
  var r = e.alternate;
  r !== null && (r.lanes |= t), Ki(e.return, t, n);
}
function pi(e, t, n, r, l) {
  var i = e.memoizedState;
  i === null ? e.memoizedState = { isBackwards: t, rendering: null, renderingStartTime: 0, last: r, tail: n, tailMode: l } : (i.isBackwards = t, i.rendering = null, i.renderingStartTime = 0, i.last = r, i.tail = n, i.tailMode = l);
}
function dc(e, t, n) {
  var r = t.pendingProps, l = r.revealOrder, i = r.tail;
  if (ve(e, t, r.children, n), r = Z.current, r & 2) r = r & 1 | 2, t.flags |= 128;
  else {
    if (e !== null && e.flags & 128) e: for (e = t.child; e !== null; ) {
      if (e.tag === 13) e.memoizedState !== null && ls(e, n, t);
      else if (e.tag === 19) ls(e, n, t);
      else if (e.child !== null) {
        e.child.return = e, e = e.child;
        continue;
      }
      if (e === t) break e;
      for (; e.sibling === null; ) {
        if (e.return === null || e.return === t) break e;
        e = e.return;
      }
      e.sibling.return = e.return, e = e.sibling;
    }
    r &= 1;
  }
  if (B(Z, r), !(t.mode & 1)) t.memoizedState = null;
  else switch (l) {
    case "forwards":
      for (n = t.child, l = null; n !== null; ) e = n.alternate, e !== null && yl(e) === null && (l = n), n = n.sibling;
      n = l, n === null ? (l = t.child, t.child = null) : (l = n.sibling, n.sibling = null), pi(t, !1, l, n, i);
      break;
    case "backwards":
      for (n = null, l = t.child, t.child = null; l !== null; ) {
        if (e = l.alternate, e !== null && yl(e) === null) {
          t.child = l;
          break;
        }
        e = l.sibling, l.sibling = n, n = l, l = e;
      }
      pi(t, !0, n, null, i);
      break;
    case "together":
      pi(t, !1, null, null, void 0);
      break;
    default:
      t.memoizedState = null;
  }
  return t.child;
}
function qr(e, t) {
  !(t.mode & 1) && e !== null && (e.alternate = null, t.alternate = null, t.flags |= 2);
}
function dt(e, t, n) {
  if (e !== null && (t.dependencies = e.dependencies), Yt |= t.lanes, !(n & t.childLanes)) return null;
  if (e !== null && t.child !== e.child) throw Error(y(153));
  if (t.child !== null) {
    for (e = t.child, n = Lt(e, e.pendingProps), t.child = n, n.return = t; e.sibling !== null; ) e = e.sibling, n = n.sibling = Lt(e, e.pendingProps), n.return = t;
    n.sibling = null;
  }
  return t.child;
}
function Qf(e, t, n) {
  switch (t.tag) {
    case 3:
      ac(t), _n();
      break;
    case 5:
      Da(t);
      break;
    case 1:
      Ee(t.type) && fl(t);
      break;
    case 4:
      Wo(t, t.stateNode.containerInfo);
      break;
    case 10:
      var r = t.type._context, l = t.memoizedProps.value;
      B(hl, r._currentValue), r._currentValue = l;
      break;
    case 13:
      if (r = t.memoizedState, r !== null)
        return r.dehydrated !== null ? (B(Z, Z.current & 1), t.flags |= 128, null) : n & t.child.childLanes ? cc(e, t, n) : (B(Z, Z.current & 1), e = dt(e, t, n), e !== null ? e.sibling : null);
      B(Z, Z.current & 1);
      break;
    case 19:
      if (r = (n & t.childLanes) !== 0, e.flags & 128) {
        if (r) return dc(e, t, n);
        t.flags |= 128;
      }
      if (l = t.memoizedState, l !== null && (l.rendering = null, l.tail = null, l.lastEffect = null), B(Z, Z.current), r) break;
      return null;
    case 22:
    case 23:
      return t.lanes = 0, uc(e, t, n);
  }
  return dt(e, t, n);
}
var fc, eo, pc, mc;
fc = function(e, t) {
  for (var n = t.child; n !== null; ) {
    if (n.tag === 5 || n.tag === 6) e.appendChild(n.stateNode);
    else if (n.tag !== 4 && n.child !== null) {
      n.child.return = n, n = n.child;
      continue;
    }
    if (n === t) break;
    for (; n.sibling === null; ) {
      if (n.return === null || n.return === t) return;
      n = n.return;
    }
    n.sibling.return = n.return, n = n.sibling;
  }
};
eo = function() {
};
pc = function(e, t, n, r) {
  var l = e.memoizedProps;
  if (l !== r) {
    e = t.stateNode, Bt(qe.current);
    var i = null;
    switch (n) {
      case "input":
        l = xi(e, l), r = xi(e, r), i = [];
        break;
      case "select":
        l = q({}, l, { value: void 0 }), r = q({}, r, { value: void 0 }), i = [];
        break;
      case "textarea":
        l = zi(e, l), r = zi(e, r), i = [];
        break;
      default:
        typeof l.onClick != "function" && typeof r.onClick == "function" && (e.onclick = cl);
    }
    Ni(n, r);
    var o;
    n = null;
    for (c in l) if (!r.hasOwnProperty(c) && l.hasOwnProperty(c) && l[c] != null) if (c === "style") {
      var u = l[c];
      for (o in u) u.hasOwnProperty(o) && (n || (n = {}), n[o] = "");
    } else c !== "dangerouslySetInnerHTML" && c !== "children" && c !== "suppressContentEditableWarning" && c !== "suppressHydrationWarning" && c !== "autoFocus" && (rr.hasOwnProperty(c) ? i || (i = []) : (i = i || []).push(c, null));
    for (c in r) {
      var s = r[c];
      if (u = l != null ? l[c] : void 0, r.hasOwnProperty(c) && s !== u && (s != null || u != null)) if (c === "style") if (u) {
        for (o in u) !u.hasOwnProperty(o) || s && s.hasOwnProperty(o) || (n || (n = {}), n[o] = "");
        for (o in s) s.hasOwnProperty(o) && u[o] !== s[o] && (n || (n = {}), n[o] = s[o]);
      } else n || (i || (i = []), i.push(
        c,
        n
      )), n = s;
      else c === "dangerouslySetInnerHTML" ? (s = s ? s.__html : void 0, u = u ? u.__html : void 0, s != null && u !== s && (i = i || []).push(c, s)) : c === "children" ? typeof s != "string" && typeof s != "number" || (i = i || []).push(c, "" + s) : c !== "suppressContentEditableWarning" && c !== "suppressHydrationWarning" && (rr.hasOwnProperty(c) ? (s != null && c === "onScroll" && Q("scroll", e), i || u === s || (i = [])) : (i = i || []).push(c, s));
    }
    n && (i = i || []).push("style", n);
    var c = i;
    (t.updateQueue = c) && (t.flags |= 4);
  }
};
mc = function(e, t, n, r) {
  n !== r && (t.flags |= 4);
};
function $n(e, t) {
  if (!G) switch (e.tailMode) {
    case "hidden":
      t = e.tail;
      for (var n = null; t !== null; ) t.alternate !== null && (n = t), t = t.sibling;
      n === null ? e.tail = null : n.sibling = null;
      break;
    case "collapsed":
      n = e.tail;
      for (var r = null; n !== null; ) n.alternate !== null && (r = n), n = n.sibling;
      r === null ? t || e.tail === null ? e.tail = null : e.tail.sibling = null : r.sibling = null;
  }
}
function pe(e) {
  var t = e.alternate !== null && e.alternate.child === e.child, n = 0, r = 0;
  if (t) for (var l = e.child; l !== null; ) n |= l.lanes | l.childLanes, r |= l.subtreeFlags & 14680064, r |= l.flags & 14680064, l.return = e, l = l.sibling;
  else for (l = e.child; l !== null; ) n |= l.lanes | l.childLanes, r |= l.subtreeFlags, r |= l.flags, l.return = e, l = l.sibling;
  return e.subtreeFlags |= r, e.childLanes = n, t;
}
function Kf(e, t, n) {
  var r = t.pendingProps;
  switch (Lo(t), t.tag) {
    case 2:
    case 16:
    case 15:
    case 0:
    case 11:
    case 7:
    case 8:
    case 12:
    case 9:
    case 14:
      return pe(t), null;
    case 1:
      return Ee(t.type) && dl(), pe(t), null;
    case 3:
      return r = t.stateNode, zn(), K(_e), K(he), Uo(), r.pendingContext && (r.context = r.pendingContext, r.pendingContext = null), (e === null || e.child === null) && (Dr(t) ? t.flags |= 4 : e === null || e.memoizedState.isDehydrated && !(t.flags & 256) || (t.flags |= 1024, Be !== null && (so(Be), Be = null))), eo(e, t), pe(t), null;
    case 5:
      Io(t);
      var l = Bt(hr.current);
      if (n = t.type, e !== null && t.stateNode != null) pc(e, t, n, r, l), e.ref !== t.ref && (t.flags |= 512, t.flags |= 2097152);
      else {
        if (!r) {
          if (t.stateNode === null) throw Error(y(166));
          return pe(t), null;
        }
        if (e = Bt(qe.current), Dr(t)) {
          r = t.stateNode, n = t.type;
          var i = t.memoizedProps;
          switch (r[Xe] = t, r[pr] = i, e = (t.mode & 1) !== 0, n) {
            case "dialog":
              Q("cancel", r), Q("close", r);
              break;
            case "iframe":
            case "object":
            case "embed":
              Q("load", r);
              break;
            case "video":
            case "audio":
              for (l = 0; l < Gn.length; l++) Q(Gn[l], r);
              break;
            case "source":
              Q("error", r);
              break;
            case "img":
            case "image":
            case "link":
              Q(
                "error",
                r
              ), Q("load", r);
              break;
            case "details":
              Q("toggle", r);
              break;
            case "input":
              pu(r, i), Q("invalid", r);
              break;
            case "select":
              r._wrapperState = { wasMultiple: !!i.multiple }, Q("invalid", r);
              break;
            case "textarea":
              hu(r, i), Q("invalid", r);
          }
          Ni(n, i), l = null;
          for (var o in i) if (i.hasOwnProperty(o)) {
            var u = i[o];
            o === "children" ? typeof u == "string" ? r.textContent !== u && (i.suppressHydrationWarning !== !0 && Or(r.textContent, u, e), l = ["children", u]) : typeof u == "number" && r.textContent !== "" + u && (i.suppressHydrationWarning !== !0 && Or(
              r.textContent,
              u,
              e
            ), l = ["children", "" + u]) : rr.hasOwnProperty(o) && u != null && o === "onScroll" && Q("scroll", r);
          }
          switch (n) {
            case "input":
              jr(r), mu(r, i, !0);
              break;
            case "textarea":
              jr(r), gu(r);
              break;
            case "select":
            case "option":
              break;
            default:
              typeof i.onClick == "function" && (r.onclick = cl);
          }
          r = l, t.updateQueue = r, r !== null && (t.flags |= 4);
        } else {
          o = l.nodeType === 9 ? l : l.ownerDocument, e === "http://www.w3.org/1999/xhtml" && (e = Bs(n)), e === "http://www.w3.org/1999/xhtml" ? n === "script" ? (e = o.createElement("div"), e.innerHTML = "<script><\/script>", e = e.removeChild(e.firstChild)) : typeof r.is == "string" ? e = o.createElement(n, { is: r.is }) : (e = o.createElement(n), n === "select" && (o = e, r.multiple ? o.multiple = !0 : r.size && (o.size = r.size))) : e = o.createElementNS(e, n), e[Xe] = t, e[pr] = r, fc(e, t, !1, !1), t.stateNode = e;
          e: {
            switch (o = ji(n, r), n) {
              case "dialog":
                Q("cancel", e), Q("close", e), l = r;
                break;
              case "iframe":
              case "object":
              case "embed":
                Q("load", e), l = r;
                break;
              case "video":
              case "audio":
                for (l = 0; l < Gn.length; l++) Q(Gn[l], e);
                l = r;
                break;
              case "source":
                Q("error", e), l = r;
                break;
              case "img":
              case "image":
              case "link":
                Q(
                  "error",
                  e
                ), Q("load", e), l = r;
                break;
              case "details":
                Q("toggle", e), l = r;
                break;
              case "input":
                pu(e, r), l = xi(e, r), Q("invalid", e);
                break;
              case "option":
                l = r;
                break;
              case "select":
                e._wrapperState = { wasMultiple: !!r.multiple }, l = q({}, r, { value: void 0 }), Q("invalid", e);
                break;
              case "textarea":
                hu(e, r), l = zi(e, r), Q("invalid", e);
                break;
              default:
                l = r;
            }
            Ni(n, l), u = l;
            for (i in u) if (u.hasOwnProperty(i)) {
              var s = u[i];
              i === "style" ? Qs(e, s) : i === "dangerouslySetInnerHTML" ? (s = s ? s.__html : void 0, s != null && Vs(e, s)) : i === "children" ? typeof s == "string" ? (n !== "textarea" || s !== "") && lr(e, s) : typeof s == "number" && lr(e, "" + s) : i !== "suppressContentEditableWarning" && i !== "suppressHydrationWarning" && i !== "autoFocus" && (rr.hasOwnProperty(i) ? s != null && i === "onScroll" && Q("scroll", e) : s != null && go(e, i, s, o));
            }
            switch (n) {
              case "input":
                jr(e), mu(e, r, !1);
                break;
              case "textarea":
                jr(e), gu(e);
                break;
              case "option":
                r.value != null && e.setAttribute("value", "" + Ft(r.value));
                break;
              case "select":
                e.multiple = !!r.multiple, i = r.value, i != null ? hn(e, !!r.multiple, i, !1) : r.defaultValue != null && hn(
                  e,
                  !!r.multiple,
                  r.defaultValue,
                  !0
                );
                break;
              default:
                typeof l.onClick == "function" && (e.onclick = cl);
            }
            switch (n) {
              case "button":
              case "input":
              case "select":
              case "textarea":
                r = !!r.autoFocus;
                break e;
              case "img":
                r = !0;
                break e;
              default:
                r = !1;
            }
          }
          r && (t.flags |= 4);
        }
        t.ref !== null && (t.flags |= 512, t.flags |= 2097152);
      }
      return pe(t), null;
    case 6:
      if (e && t.stateNode != null) mc(e, t, e.memoizedProps, r);
      else {
        if (typeof r != "string" && t.stateNode === null) throw Error(y(166));
        if (n = Bt(hr.current), Bt(qe.current), Dr(t)) {
          if (r = t.stateNode, n = t.memoizedProps, r[Xe] = t, (i = r.nodeValue !== n) && (e = je, e !== null)) switch (e.tag) {
            case 3:
              Or(r.nodeValue, n, (e.mode & 1) !== 0);
              break;
            case 5:
              e.memoizedProps.suppressHydrationWarning !== !0 && Or(r.nodeValue, n, (e.mode & 1) !== 0);
          }
          i && (t.flags |= 4);
        } else r = (n.nodeType === 9 ? n : n.ownerDocument).createTextNode(r), r[Xe] = t, t.stateNode = r;
      }
      return pe(t), null;
    case 13:
      if (K(Z), r = t.memoizedState, e === null || e.memoizedState !== null && e.memoizedState.dehydrated !== null) {
        if (G && Ne !== null && t.mode & 1 && !(t.flags & 128)) Fa(), _n(), t.flags |= 98560, i = !1;
        else if (i = Dr(t), r !== null && r.dehydrated !== null) {
          if (e === null) {
            if (!i) throw Error(y(318));
            if (i = t.memoizedState, i = i !== null ? i.dehydrated : null, !i) throw Error(y(317));
            i[Xe] = t;
          } else _n(), !(t.flags & 128) && (t.memoizedState = null), t.flags |= 4;
          pe(t), i = !1;
        } else Be !== null && (so(Be), Be = null), i = !0;
        if (!i) return t.flags & 65536 ? t : null;
      }
      return t.flags & 128 ? (t.lanes = n, t) : (r = r !== null, r !== (e !== null && e.memoizedState !== null) && r && (t.child.flags |= 8192, t.mode & 1 && (e === null || Z.current & 1 ? le === 0 && (le = 3) : qo())), t.updateQueue !== null && (t.flags |= 4), pe(t), null);
    case 4:
      return zn(), eo(e, t), e === null && dr(t.stateNode.containerInfo), pe(t), null;
    case 10:
      return Ro(t.type._context), pe(t), null;
    case 17:
      return Ee(t.type) && dl(), pe(t), null;
    case 19:
      if (K(Z), i = t.memoizedState, i === null) return pe(t), null;
      if (r = (t.flags & 128) !== 0, o = i.rendering, o === null) if (r) $n(i, !1);
      else {
        if (le !== 0 || e !== null && e.flags & 128) for (e = t.child; e !== null; ) {
          if (o = yl(e), o !== null) {
            for (t.flags |= 128, $n(i, !1), r = o.updateQueue, r !== null && (t.updateQueue = r, t.flags |= 4), t.subtreeFlags = 0, r = n, n = t.child; n !== null; ) i = n, e = r, i.flags &= 14680066, o = i.alternate, o === null ? (i.childLanes = 0, i.lanes = e, i.child = null, i.subtreeFlags = 0, i.memoizedProps = null, i.memoizedState = null, i.updateQueue = null, i.dependencies = null, i.stateNode = null) : (i.childLanes = o.childLanes, i.lanes = o.lanes, i.child = o.child, i.subtreeFlags = 0, i.deletions = null, i.memoizedProps = o.memoizedProps, i.memoizedState = o.memoizedState, i.updateQueue = o.updateQueue, i.type = o.type, e = o.dependencies, i.dependencies = e === null ? null : { lanes: e.lanes, firstContext: e.firstContext }), n = n.sibling;
            return B(Z, Z.current & 1 | 2), t.child;
          }
          e = e.sibling;
        }
        i.tail !== null && ee() > Nn && (t.flags |= 128, r = !0, $n(i, !1), t.lanes = 4194304);
      }
      else {
        if (!r) if (e = yl(o), e !== null) {
          if (t.flags |= 128, r = !0, n = e.updateQueue, n !== null && (t.updateQueue = n, t.flags |= 4), $n(i, !0), i.tail === null && i.tailMode === "hidden" && !o.alternate && !G) return pe(t), null;
        } else 2 * ee() - i.renderingStartTime > Nn && n !== 1073741824 && (t.flags |= 128, r = !0, $n(i, !1), t.lanes = 4194304);
        i.isBackwards ? (o.sibling = t.child, t.child = o) : (n = i.last, n !== null ? n.sibling = o : t.child = o, i.last = o);
      }
      return i.tail !== null ? (t = i.tail, i.rendering = t, i.tail = t.sibling, i.renderingStartTime = ee(), t.sibling = null, n = Z.current, B(Z, r ? n & 1 | 2 : n & 1), t) : (pe(t), null);
    case 22:
    case 23:
      return Jo(), r = t.memoizedState !== null, e !== null && e.memoizedState !== null !== r && (t.flags |= 8192), r && t.mode & 1 ? Ce & 1073741824 && (pe(t), t.subtreeFlags & 6 && (t.flags |= 8192)) : pe(t), null;
    case 24:
      return null;
    case 25:
      return null;
  }
  throw Error(y(156, t.tag));
}
function Gf(e, t) {
  switch (Lo(t), t.tag) {
    case 1:
      return Ee(t.type) && dl(), e = t.flags, e & 65536 ? (t.flags = e & -65537 | 128, t) : null;
    case 3:
      return zn(), K(_e), K(he), Uo(), e = t.flags, e & 65536 && !(e & 128) ? (t.flags = e & -65537 | 128, t) : null;
    case 5:
      return Io(t), null;
    case 13:
      if (K(Z), e = t.memoizedState, e !== null && e.dehydrated !== null) {
        if (t.alternate === null) throw Error(y(340));
        _n();
      }
      return e = t.flags, e & 65536 ? (t.flags = e & -65537 | 128, t) : null;
    case 19:
      return K(Z), null;
    case 4:
      return zn(), null;
    case 10:
      return Ro(t.type._context), null;
    case 22:
    case 23:
      return Jo(), null;
    case 24:
      return null;
    default:
      return null;
  }
}
var Ur = !1, me = !1, Yf = typeof WeakSet == "function" ? WeakSet : Set, C = null;
function pn(e, t) {
  var n = e.ref;
  if (n !== null) if (typeof n == "function") try {
    n(null);
  } catch (r) {
    b(e, t, r);
  }
  else n.current = null;
}
function to(e, t, n) {
  try {
    n();
  } catch (r) {
    b(e, t, r);
  }
}
var is = !1;
function Xf(e, t) {
  if (Wi = ul, e = ka(), Po(e)) {
    if ("selectionStart" in e) var n = { start: e.selectionStart, end: e.selectionEnd };
    else e: {
      n = (n = e.ownerDocument) && n.defaultView || window;
      var r = n.getSelection && n.getSelection();
      if (r && r.rangeCount !== 0) {
        n = r.anchorNode;
        var l = r.anchorOffset, i = r.focusNode;
        r = r.focusOffset;
        try {
          n.nodeType, i.nodeType;
        } catch {
          n = null;
          break e;
        }
        var o = 0, u = -1, s = -1, c = 0, m = 0, h = e, g = null;
        t: for (; ; ) {
          for (var k; h !== n || l !== 0 && h.nodeType !== 3 || (u = o + l), h !== i || r !== 0 && h.nodeType !== 3 || (s = o + r), h.nodeType === 3 && (o += h.nodeValue.length), (k = h.firstChild) !== null; )
            g = h, h = k;
          for (; ; ) {
            if (h === e) break t;
            if (g === n && ++c === l && (u = o), g === i && ++m === r && (s = o), (k = h.nextSibling) !== null) break;
            h = g, g = h.parentNode;
          }
          h = k;
        }
        n = u === -1 || s === -1 ? null : { start: u, end: s };
      } else n = null;
    }
    n = n || { start: 0, end: 0 };
  } else n = null;
  for (Ii = { focusedElem: e, selectionRange: n }, ul = !1, C = t; C !== null; ) if (t = C, e = t.child, (t.subtreeFlags & 1028) !== 0 && e !== null) e.return = t, C = e;
  else for (; C !== null; ) {
    t = C;
    try {
      var x = t.alternate;
      if (t.flags & 1024) switch (t.tag) {
        case 0:
        case 11:
        case 15:
          break;
        case 1:
          if (x !== null) {
            var w = x.memoizedProps, U = x.memoizedState, f = t.stateNode, a = f.getSnapshotBeforeUpdate(t.elementType === t.type ? w : Ue(t.type, w), U);
            f.__reactInternalSnapshotBeforeUpdate = a;
          }
          break;
        case 3:
          var p = t.stateNode.containerInfo;
          p.nodeType === 1 ? p.textContent = "" : p.nodeType === 9 && p.documentElement && p.removeChild(p.documentElement);
          break;
        case 5:
        case 6:
        case 4:
        case 17:
          break;
        default:
          throw Error(y(163));
      }
    } catch (v) {
      b(t, t.return, v);
    }
    if (e = t.sibling, e !== null) {
      e.return = t.return, C = e;
      break;
    }
    C = t.return;
  }
  return x = is, is = !1, x;
}
function er(e, t, n) {
  var r = t.updateQueue;
  if (r = r !== null ? r.lastEffect : null, r !== null) {
    var l = r = r.next;
    do {
      if ((l.tag & e) === e) {
        var i = l.destroy;
        l.destroy = void 0, i !== void 0 && to(t, n, i);
      }
      l = l.next;
    } while (l !== r);
  }
}
function Rl(e, t) {
  if (t = t.updateQueue, t = t !== null ? t.lastEffect : null, t !== null) {
    var n = t = t.next;
    do {
      if ((n.tag & e) === e) {
        var r = n.create;
        n.destroy = r();
      }
      n = n.next;
    } while (n !== t);
  }
}
function no(e) {
  var t = e.ref;
  if (t !== null) {
    var n = e.stateNode;
    switch (e.tag) {
      case 5:
        e = n;
        break;
      default:
        e = n;
    }
    typeof t == "function" ? t(e) : t.current = e;
  }
}
function hc(e) {
  var t = e.alternate;
  t !== null && (e.alternate = null, hc(t)), e.child = null, e.deletions = null, e.sibling = null, e.tag === 5 && (t = e.stateNode, t !== null && (delete t[Xe], delete t[pr], delete t[Bi], delete t[Lf], delete t[Ff])), e.stateNode = null, e.return = null, e.dependencies = null, e.memoizedProps = null, e.memoizedState = null, e.pendingProps = null, e.stateNode = null, e.updateQueue = null;
}
function gc(e) {
  return e.tag === 5 || e.tag === 3 || e.tag === 4;
}
function os(e) {
  e: for (; ; ) {
    for (; e.sibling === null; ) {
      if (e.return === null || gc(e.return)) return null;
      e = e.return;
    }
    for (e.sibling.return = e.return, e = e.sibling; e.tag !== 5 && e.tag !== 6 && e.tag !== 18; ) {
      if (e.flags & 2 || e.child === null || e.tag === 4) continue e;
      e.child.return = e, e = e.child;
    }
    if (!(e.flags & 2)) return e.stateNode;
  }
}
function ro(e, t, n) {
  var r = e.tag;
  if (r === 5 || r === 6) e = e.stateNode, t ? n.nodeType === 8 ? n.parentNode.insertBefore(e, t) : n.insertBefore(e, t) : (n.nodeType === 8 ? (t = n.parentNode, t.insertBefore(e, n)) : (t = n, t.appendChild(e)), n = n._reactRootContainer, n != null || t.onclick !== null || (t.onclick = cl));
  else if (r !== 4 && (e = e.child, e !== null)) for (ro(e, t, n), e = e.sibling; e !== null; ) ro(e, t, n), e = e.sibling;
}
function lo(e, t, n) {
  var r = e.tag;
  if (r === 5 || r === 6) e = e.stateNode, t ? n.insertBefore(e, t) : n.appendChild(e);
  else if (r !== 4 && (e = e.child, e !== null)) for (lo(e, t, n), e = e.sibling; e !== null; ) lo(e, t, n), e = e.sibling;
}
var se = null, $e = !1;
function vt(e, t, n) {
  for (n = n.child; n !== null; ) vc(e, t, n), n = n.sibling;
}
function vc(e, t, n) {
  if (Je && typeof Je.onCommitFiberUnmount == "function") try {
    Je.onCommitFiberUnmount(Nl, n);
  } catch {
  }
  switch (n.tag) {
    case 5:
      me || pn(n, t);
    case 6:
      var r = se, l = $e;
      se = null, vt(e, t, n), se = r, $e = l, se !== null && ($e ? (e = se, n = n.stateNode, e.nodeType === 8 ? e.parentNode.removeChild(n) : e.removeChild(n)) : se.removeChild(n.stateNode));
      break;
    case 18:
      se !== null && ($e ? (e = se, n = n.stateNode, e.nodeType === 8 ? oi(e.parentNode, n) : e.nodeType === 1 && oi(e, n), sr(e)) : oi(se, n.stateNode));
      break;
    case 4:
      r = se, l = $e, se = n.stateNode.containerInfo, $e = !0, vt(e, t, n), se = r, $e = l;
      break;
    case 0:
    case 11:
    case 14:
    case 15:
      if (!me && (r = n.updateQueue, r !== null && (r = r.lastEffect, r !== null))) {
        l = r = r.next;
        do {
          var i = l, o = i.destroy;
          i = i.tag, o !== void 0 && (i & 2 || i & 4) && to(n, t, o), l = l.next;
        } while (l !== r);
      }
      vt(e, t, n);
      break;
    case 1:
      if (!me && (pn(n, t), r = n.stateNode, typeof r.componentWillUnmount == "function")) try {
        r.props = n.memoizedProps, r.state = n.memoizedState, r.componentWillUnmount();
      } catch (u) {
        b(n, t, u);
      }
      vt(e, t, n);
      break;
    case 21:
      vt(e, t, n);
      break;
    case 22:
      n.mode & 1 ? (me = (r = me) || n.memoizedState !== null, vt(e, t, n), me = r) : vt(e, t, n);
      break;
    default:
      vt(e, t, n);
  }
}
function us(e) {
  var t = e.updateQueue;
  if (t !== null) {
    e.updateQueue = null;
    var n = e.stateNode;
    n === null && (n = e.stateNode = new Yf()), t.forEach(function(r) {
      var l = lp.bind(null, e, r);
      n.has(r) || (n.add(r), r.then(l, l));
    });
  }
}
function Ie(e, t) {
  var n = t.deletions;
  if (n !== null) for (var r = 0; r < n.length; r++) {
    var l = n[r];
    try {
      var i = e, o = t, u = o;
      e: for (; u !== null; ) {
        switch (u.tag) {
          case 5:
            se = u.stateNode, $e = !1;
            break e;
          case 3:
            se = u.stateNode.containerInfo, $e = !0;
            break e;
          case 4:
            se = u.stateNode.containerInfo, $e = !0;
            break e;
        }
        u = u.return;
      }
      if (se === null) throw Error(y(160));
      vc(i, o, l), se = null, $e = !1;
      var s = l.alternate;
      s !== null && (s.return = null), l.return = null;
    } catch (c) {
      b(l, t, c);
    }
  }
  if (t.subtreeFlags & 12854) for (t = t.child; t !== null; ) yc(t, e), t = t.sibling;
}
function yc(e, t) {
  var n = e.alternate, r = e.flags;
  switch (e.tag) {
    case 0:
    case 11:
    case 14:
    case 15:
      if (Ie(t, e), Ge(e), r & 4) {
        try {
          er(3, e, e.return), Rl(3, e);
        } catch (w) {
          b(e, e.return, w);
        }
        try {
          er(5, e, e.return);
        } catch (w) {
          b(e, e.return, w);
        }
      }
      break;
    case 1:
      Ie(t, e), Ge(e), r & 512 && n !== null && pn(n, n.return);
      break;
    case 5:
      if (Ie(t, e), Ge(e), r & 512 && n !== null && pn(n, n.return), e.flags & 32) {
        var l = e.stateNode;
        try {
          lr(l, "");
        } catch (w) {
          b(e, e.return, w);
        }
      }
      if (r & 4 && (l = e.stateNode, l != null)) {
        var i = e.memoizedProps, o = n !== null ? n.memoizedProps : i, u = e.type, s = e.updateQueue;
        if (e.updateQueue = null, s !== null) try {
          u === "input" && i.type === "radio" && i.name != null && Us(l, i), ji(u, o);
          var c = ji(u, i);
          for (o = 0; o < s.length; o += 2) {
            var m = s[o], h = s[o + 1];
            m === "style" ? Qs(l, h) : m === "dangerouslySetInnerHTML" ? Vs(l, h) : m === "children" ? lr(l, h) : go(l, m, h, c);
          }
          switch (u) {
            case "input":
              _i(l, i);
              break;
            case "textarea":
              $s(l, i);
              break;
            case "select":
              var g = l._wrapperState.wasMultiple;
              l._wrapperState.wasMultiple = !!i.multiple;
              var k = i.value;
              k != null ? hn(l, !!i.multiple, k, !1) : g !== !!i.multiple && (i.defaultValue != null ? hn(
                l,
                !!i.multiple,
                i.defaultValue,
                !0
              ) : hn(l, !!i.multiple, i.multiple ? [] : "", !1));
          }
          l[pr] = i;
        } catch (w) {
          b(e, e.return, w);
        }
      }
      break;
    case 6:
      if (Ie(t, e), Ge(e), r & 4) {
        if (e.stateNode === null) throw Error(y(162));
        l = e.stateNode, i = e.memoizedProps;
        try {
          l.nodeValue = i;
        } catch (w) {
          b(e, e.return, w);
        }
      }
      break;
    case 3:
      if (Ie(t, e), Ge(e), r & 4 && n !== null && n.memoizedState.isDehydrated) try {
        sr(t.containerInfo);
      } catch (w) {
        b(e, e.return, w);
      }
      break;
    case 4:
      Ie(t, e), Ge(e);
      break;
    case 13:
      Ie(t, e), Ge(e), l = e.child, l.flags & 8192 && (i = l.memoizedState !== null, l.stateNode.isHidden = i, !i || l.alternate !== null && l.alternate.memoizedState !== null || (Xo = ee())), r & 4 && us(e);
      break;
    case 22:
      if (m = n !== null && n.memoizedState !== null, e.mode & 1 ? (me = (c = me) || m, Ie(t, e), me = c) : Ie(t, e), Ge(e), r & 8192) {
        if (c = e.memoizedState !== null, (e.stateNode.isHidden = c) && !m && e.mode & 1) for (C = e, m = e.child; m !== null; ) {
          for (h = C = m; C !== null; ) {
            switch (g = C, k = g.child, g.tag) {
              case 0:
              case 11:
              case 14:
              case 15:
                er(4, g, g.return);
                break;
              case 1:
                pn(g, g.return);
                var x = g.stateNode;
                if (typeof x.componentWillUnmount == "function") {
                  r = g, n = g.return;
                  try {
                    t = r, x.props = t.memoizedProps, x.state = t.memoizedState, x.componentWillUnmount();
                  } catch (w) {
                    b(r, n, w);
                  }
                }
                break;
              case 5:
                pn(g, g.return);
                break;
              case 22:
                if (g.memoizedState !== null) {
                  as(h);
                  continue;
                }
            }
            k !== null ? (k.return = g, C = k) : as(h);
          }
          m = m.sibling;
        }
        e: for (m = null, h = e; ; ) {
          if (h.tag === 5) {
            if (m === null) {
              m = h;
              try {
                l = h.stateNode, c ? (i = l.style, typeof i.setProperty == "function" ? i.setProperty("display", "none", "important") : i.display = "none") : (u = h.stateNode, s = h.memoizedProps.style, o = s != null && s.hasOwnProperty("display") ? s.display : null, u.style.display = Hs("display", o));
              } catch (w) {
                b(e, e.return, w);
              }
            }
          } else if (h.tag === 6) {
            if (m === null) try {
              h.stateNode.nodeValue = c ? "" : h.memoizedProps;
            } catch (w) {
              b(e, e.return, w);
            }
          } else if ((h.tag !== 22 && h.tag !== 23 || h.memoizedState === null || h === e) && h.child !== null) {
            h.child.return = h, h = h.child;
            continue;
          }
          if (h === e) break e;
          for (; h.sibling === null; ) {
            if (h.return === null || h.return === e) break e;
            m === h && (m = null), h = h.return;
          }
          m === h && (m = null), h.sibling.return = h.return, h = h.sibling;
        }
      }
      break;
    case 19:
      Ie(t, e), Ge(e), r & 4 && us(e);
      break;
    case 21:
      break;
    default:
      Ie(
        t,
        e
      ), Ge(e);
  }
}
function Ge(e) {
  var t = e.flags;
  if (t & 2) {
    try {
      e: {
        for (var n = e.return; n !== null; ) {
          if (gc(n)) {
            var r = n;
            break e;
          }
          n = n.return;
        }
        throw Error(y(160));
      }
      switch (r.tag) {
        case 5:
          var l = r.stateNode;
          r.flags & 32 && (lr(l, ""), r.flags &= -33);
          var i = os(e);
          lo(e, i, l);
          break;
        case 3:
        case 4:
          var o = r.stateNode.containerInfo, u = os(e);
          ro(e, u, o);
          break;
        default:
          throw Error(y(161));
      }
    } catch (s) {
      b(e, e.return, s);
    }
    e.flags &= -3;
  }
  t & 4096 && (e.flags &= -4097);
}
function Zf(e, t, n) {
  C = e, kc(e);
}
function kc(e, t, n) {
  for (var r = (e.mode & 1) !== 0; C !== null; ) {
    var l = C, i = l.child;
    if (l.tag === 22 && r) {
      var o = l.memoizedState !== null || Ur;
      if (!o) {
        var u = l.alternate, s = u !== null && u.memoizedState !== null || me;
        u = Ur;
        var c = me;
        if (Ur = o, (me = s) && !c) for (C = l; C !== null; ) o = C, s = o.child, o.tag === 22 && o.memoizedState !== null ? cs(l) : s !== null ? (s.return = o, C = s) : cs(l);
        for (; i !== null; ) C = i, kc(i), i = i.sibling;
        C = l, Ur = u, me = c;
      }
      ss(e);
    } else l.subtreeFlags & 8772 && i !== null ? (i.return = l, C = i) : ss(e);
  }
}
function ss(e) {
  for (; C !== null; ) {
    var t = C;
    if (t.flags & 8772) {
      var n = t.alternate;
      try {
        if (t.flags & 8772) switch (t.tag) {
          case 0:
          case 11:
          case 15:
            me || Rl(5, t);
            break;
          case 1:
            var r = t.stateNode;
            if (t.flags & 4 && !me) if (n === null) r.componentDidMount();
            else {
              var l = t.elementType === t.type ? n.memoizedProps : Ue(t.type, n.memoizedProps);
              r.componentDidUpdate(l, n.memoizedState, r.__reactInternalSnapshotBeforeUpdate);
            }
            var i = t.updateQueue;
            i !== null && Ku(t, i, r);
            break;
          case 3:
            var o = t.updateQueue;
            if (o !== null) {
              if (n = null, t.child !== null) switch (t.child.tag) {
                case 5:
                  n = t.child.stateNode;
                  break;
                case 1:
                  n = t.child.stateNode;
              }
              Ku(t, o, n);
            }
            break;
          case 5:
            var u = t.stateNode;
            if (n === null && t.flags & 4) {
              n = u;
              var s = t.memoizedProps;
              switch (t.type) {
                case "button":
                case "input":
                case "select":
                case "textarea":
                  s.autoFocus && n.focus();
                  break;
                case "img":
                  s.src && (n.src = s.src);
              }
            }
            break;
          case 6:
            break;
          case 4:
            break;
          case 12:
            break;
          case 13:
            if (t.memoizedState === null) {
              var c = t.alternate;
              if (c !== null) {
                var m = c.memoizedState;
                if (m !== null) {
                  var h = m.dehydrated;
                  h !== null && sr(h);
                }
              }
            }
            break;
          case 19:
          case 17:
          case 21:
          case 22:
          case 23:
          case 25:
            break;
          default:
            throw Error(y(163));
        }
        me || t.flags & 512 && no(t);
      } catch (g) {
        b(t, t.return, g);
      }
    }
    if (t === e) {
      C = null;
      break;
    }
    if (n = t.sibling, n !== null) {
      n.return = t.return, C = n;
      break;
    }
    C = t.return;
  }
}
function as(e) {
  for (; C !== null; ) {
    var t = C;
    if (t === e) {
      C = null;
      break;
    }
    var n = t.sibling;
    if (n !== null) {
      n.return = t.return, C = n;
      break;
    }
    C = t.return;
  }
}
function cs(e) {
  for (; C !== null; ) {
    var t = C;
    try {
      switch (t.tag) {
        case 0:
        case 11:
        case 15:
          var n = t.return;
          try {
            Rl(4, t);
          } catch (s) {
            b(t, n, s);
          }
          break;
        case 1:
          var r = t.stateNode;
          if (typeof r.componentDidMount == "function") {
            var l = t.return;
            try {
              r.componentDidMount();
            } catch (s) {
              b(t, l, s);
            }
          }
          var i = t.return;
          try {
            no(t);
          } catch (s) {
            b(t, i, s);
          }
          break;
        case 5:
          var o = t.return;
          try {
            no(t);
          } catch (s) {
            b(t, o, s);
          }
      }
    } catch (s) {
      b(t, t.return, s);
    }
    if (t === e) {
      C = null;
      break;
    }
    var u = t.sibling;
    if (u !== null) {
      u.return = t.return, C = u;
      break;
    }
    C = t.return;
  }
}
var Jf = Math.ceil, Sl = ft.ReactCurrentDispatcher, Go = ft.ReactCurrentOwner, Re = ft.ReactCurrentBatchConfig, D = 0, ue = null, ne = null, ae = 0, Ce = 0, mn = Rt(0), le = 0, kr = null, Yt = 0, Ol = 0, Yo = 0, tr = null, Se = null, Xo = 0, Nn = 1 / 0, rt = null, xl = !1, io = null, Pt = null, $r = !1, _t = null, _l = 0, nr = 0, oo = null, br = -1, el = 0;
function ye() {
  return D & 6 ? ee() : br !== -1 ? br : br = ee();
}
function Tt(e) {
  return e.mode & 1 ? D & 2 && ae !== 0 ? ae & -ae : Af.transition !== null ? (el === 0 && (el = ra()), el) : (e = I, e !== 0 || (e = window.event, e = e === void 0 ? 16 : ca(e.type)), e) : 1;
}
function He(e, t, n, r) {
  if (50 < nr) throw nr = 0, oo = null, Error(y(185));
  Sr(e, n, r), (!(D & 2) || e !== ue) && (e === ue && (!(D & 2) && (Ol |= n), le === 4 && St(e, ae)), ze(e, r), n === 1 && D === 0 && !(t.mode & 1) && (Nn = ee() + 500, Fl && Ot()));
}
function ze(e, t) {
  var n = e.callbackNode;
  Md(e, t);
  var r = ol(e, e === ue ? ae : 0);
  if (r === 0) n !== null && ku(n), e.callbackNode = null, e.callbackPriority = 0;
  else if (t = r & -r, e.callbackPriority !== t) {
    if (n != null && ku(n), t === 1) e.tag === 0 ? Mf(ds.bind(null, e)) : Pa(ds.bind(null, e)), Pf(function() {
      !(D & 6) && Ot();
    }), n = null;
    else {
      switch (la(r)) {
        case 1:
          n = So;
          break;
        case 4:
          n = ta;
          break;
        case 16:
          n = il;
          break;
        case 536870912:
          n = na;
          break;
        default:
          n = il;
      }
      n = Nc(n, wc.bind(null, e));
    }
    e.callbackPriority = t, e.callbackNode = n;
  }
}
function wc(e, t) {
  if (br = -1, el = 0, D & 6) throw Error(y(327));
  var n = e.callbackNode;
  if (wn() && e.callbackNode !== n) return null;
  var r = ol(e, e === ue ? ae : 0);
  if (r === 0) return null;
  if (r & 30 || r & e.expiredLanes || t) t = El(e, r);
  else {
    t = r;
    var l = D;
    D |= 2;
    var i = xc();
    (ue !== e || ae !== t) && (rt = null, Nn = ee() + 500, Vt(e, t));
    do
      try {
        ep();
        break;
      } catch (u) {
        Sc(e, u);
      }
    while (!0);
    Ao(), Sl.current = i, D = l, ne !== null ? t = 0 : (ue = null, ae = 0, t = le);
  }
  if (t !== 0) {
    if (t === 2 && (l = Mi(e), l !== 0 && (r = l, t = uo(e, l))), t === 1) throw n = kr, Vt(e, 0), St(e, r), ze(e, ee()), n;
    if (t === 6) St(e, r);
    else {
      if (l = e.current.alternate, !(r & 30) && !qf(l) && (t = El(e, r), t === 2 && (i = Mi(e), i !== 0 && (r = i, t = uo(e, i))), t === 1)) throw n = kr, Vt(e, 0), St(e, r), ze(e, ee()), n;
      switch (e.finishedWork = l, e.finishedLanes = r, t) {
        case 0:
        case 1:
          throw Error(y(345));
        case 2:
          It(e, Se, rt);
          break;
        case 3:
          if (St(e, r), (r & 130023424) === r && (t = Xo + 500 - ee(), 10 < t)) {
            if (ol(e, 0) !== 0) break;
            if (l = e.suspendedLanes, (l & r) !== r) {
              ye(), e.pingedLanes |= e.suspendedLanes & l;
              break;
            }
            e.timeoutHandle = $i(It.bind(null, e, Se, rt), t);
            break;
          }
          It(e, Se, rt);
          break;
        case 4:
          if (St(e, r), (r & 4194240) === r) break;
          for (t = e.eventTimes, l = -1; 0 < r; ) {
            var o = 31 - Ve(r);
            i = 1 << o, o = t[o], o > l && (l = o), r &= ~i;
          }
          if (r = l, r = ee() - r, r = (120 > r ? 120 : 480 > r ? 480 : 1080 > r ? 1080 : 1920 > r ? 1920 : 3e3 > r ? 3e3 : 4320 > r ? 4320 : 1960 * Jf(r / 1960)) - r, 10 < r) {
            e.timeoutHandle = $i(It.bind(null, e, Se, rt), r);
            break;
          }
          It(e, Se, rt);
          break;
        case 5:
          It(e, Se, rt);
          break;
        default:
          throw Error(y(329));
      }
    }
  }
  return ze(e, ee()), e.callbackNode === n ? wc.bind(null, e) : null;
}
function uo(e, t) {
  var n = tr;
  return e.current.memoizedState.isDehydrated && (Vt(e, t).flags |= 256), e = El(e, t), e !== 2 && (t = Se, Se = n, t !== null && so(t)), e;
}
function so(e) {
  Se === null ? Se = e : Se.push.apply(Se, e);
}
function qf(e) {
  for (var t = e; ; ) {
    if (t.flags & 16384) {
      var n = t.updateQueue;
      if (n !== null && (n = n.stores, n !== null)) for (var r = 0; r < n.length; r++) {
        var l = n[r], i = l.getSnapshot;
        l = l.value;
        try {
          if (!Qe(i(), l)) return !1;
        } catch {
          return !1;
        }
      }
    }
    if (n = t.child, t.subtreeFlags & 16384 && n !== null) n.return = t, t = n;
    else {
      if (t === e) break;
      for (; t.sibling === null; ) {
        if (t.return === null || t.return === e) return !0;
        t = t.return;
      }
      t.sibling.return = t.return, t = t.sibling;
    }
  }
  return !0;
}
function St(e, t) {
  for (t &= ~Yo, t &= ~Ol, e.suspendedLanes |= t, e.pingedLanes &= ~t, e = e.expirationTimes; 0 < t; ) {
    var n = 31 - Ve(t), r = 1 << n;
    e[n] = -1, t &= ~r;
  }
}
function ds(e) {
  if (D & 6) throw Error(y(327));
  wn();
  var t = ol(e, 0);
  if (!(t & 1)) return ze(e, ee()), null;
  var n = El(e, t);
  if (e.tag !== 0 && n === 2) {
    var r = Mi(e);
    r !== 0 && (t = r, n = uo(e, r));
  }
  if (n === 1) throw n = kr, Vt(e, 0), St(e, t), ze(e, ee()), n;
  if (n === 6) throw Error(y(345));
  return e.finishedWork = e.current.alternate, e.finishedLanes = t, It(e, Se, rt), ze(e, ee()), null;
}
function Zo(e, t) {
  var n = D;
  D |= 1;
  try {
    return e(t);
  } finally {
    D = n, D === 0 && (Nn = ee() + 500, Fl && Ot());
  }
}
function Xt(e) {
  _t !== null && _t.tag === 0 && !(D & 6) && wn();
  var t = D;
  D |= 1;
  var n = Re.transition, r = I;
  try {
    if (Re.transition = null, I = 1, e) return e();
  } finally {
    I = r, Re.transition = n, D = t, !(D & 6) && Ot();
  }
}
function Jo() {
  Ce = mn.current, K(mn);
}
function Vt(e, t) {
  e.finishedWork = null, e.finishedLanes = 0;
  var n = e.timeoutHandle;
  if (n !== -1 && (e.timeoutHandle = -1, jf(n)), ne !== null) for (n = ne.return; n !== null; ) {
    var r = n;
    switch (Lo(r), r.tag) {
      case 1:
        r = r.type.childContextTypes, r != null && dl();
        break;
      case 3:
        zn(), K(_e), K(he), Uo();
        break;
      case 5:
        Io(r);
        break;
      case 4:
        zn();
        break;
      case 13:
        K(Z);
        break;
      case 19:
        K(Z);
        break;
      case 10:
        Ro(r.type._context);
        break;
      case 22:
      case 23:
        Jo();
    }
    n = n.return;
  }
  if (ue = e, ne = e = Lt(e.current, null), ae = Ce = t, le = 0, kr = null, Yo = Ol = Yt = 0, Se = tr = null, $t !== null) {
    for (t = 0; t < $t.length; t++) if (n = $t[t], r = n.interleaved, r !== null) {
      n.interleaved = null;
      var l = r.next, i = n.pending;
      if (i !== null) {
        var o = i.next;
        i.next = l, r.next = o;
      }
      n.pending = r;
    }
    $t = null;
  }
  return e;
}
function Sc(e, t) {
  do {
    var n = ne;
    try {
      if (Ao(), Zr.current = wl, kl) {
        for (var r = J.memoizedState; r !== null; ) {
          var l = r.queue;
          l !== null && (l.pending = null), r = r.next;
        }
        kl = !1;
      }
      if (Gt = 0, oe = re = J = null, bn = !1, gr = 0, Go.current = null, n === null || n.return === null) {
        le = 1, kr = t, ne = null;
        break;
      }
      e: {
        var i = e, o = n.return, u = n, s = t;
        if (t = ae, u.flags |= 32768, s !== null && typeof s == "object" && typeof s.then == "function") {
          var c = s, m = u, h = m.tag;
          if (!(m.mode & 1) && (h === 0 || h === 11 || h === 15)) {
            var g = m.alternate;
            g ? (m.updateQueue = g.updateQueue, m.memoizedState = g.memoizedState, m.lanes = g.lanes) : (m.updateQueue = null, m.memoizedState = null);
          }
          var k = qu(o);
          if (k !== null) {
            k.flags &= -257, bu(k, o, u, i, t), k.mode & 1 && Ju(i, c, t), t = k, s = c;
            var x = t.updateQueue;
            if (x === null) {
              var w = /* @__PURE__ */ new Set();
              w.add(s), t.updateQueue = w;
            } else x.add(s);
            break e;
          } else {
            if (!(t & 1)) {
              Ju(i, c, t), qo();
              break e;
            }
            s = Error(y(426));
          }
        } else if (G && u.mode & 1) {
          var U = qu(o);
          if (U !== null) {
            !(U.flags & 65536) && (U.flags |= 256), bu(U, o, u, i, t), Fo(Cn(s, u));
            break e;
          }
        }
        i = s = Cn(s, u), le !== 4 && (le = 2), tr === null ? tr = [i] : tr.push(i), i = o;
        do {
          switch (i.tag) {
            case 3:
              i.flags |= 65536, t &= -t, i.lanes |= t;
              var f = lc(i, s, t);
              Qu(i, f);
              break e;
            case 1:
              u = s;
              var a = i.type, p = i.stateNode;
              if (!(i.flags & 128) && (typeof a.getDerivedStateFromError == "function" || p !== null && typeof p.componentDidCatch == "function" && (Pt === null || !Pt.has(p)))) {
                i.flags |= 65536, t &= -t, i.lanes |= t;
                var v = ic(i, u, t);
                Qu(i, v);
                break e;
              }
          }
          i = i.return;
        } while (i !== null);
      }
      Ec(n);
    } catch (E) {
      t = E, ne === n && n !== null && (ne = n = n.return);
      continue;
    }
    break;
  } while (!0);
}
function xc() {
  var e = Sl.current;
  return Sl.current = wl, e === null ? wl : e;
}
function qo() {
  (le === 0 || le === 3 || le === 2) && (le = 4), ue === null || !(Yt & 268435455) && !(Ol & 268435455) || St(ue, ae);
}
function El(e, t) {
  var n = D;
  D |= 2;
  var r = xc();
  (ue !== e || ae !== t) && (rt = null, Vt(e, t));
  do
    try {
      bf();
      break;
    } catch (l) {
      Sc(e, l);
    }
  while (!0);
  if (Ao(), D = n, Sl.current = r, ne !== null) throw Error(y(261));
  return ue = null, ae = 0, le;
}
function bf() {
  for (; ne !== null; ) _c(ne);
}
function ep() {
  for (; ne !== null && !Ed(); ) _c(ne);
}
function _c(e) {
  var t = Cc(e.alternate, e, Ce);
  e.memoizedProps = e.pendingProps, t === null ? Ec(e) : ne = t, Go.current = null;
}
function Ec(e) {
  var t = e;
  do {
    var n = t.alternate;
    if (e = t.return, t.flags & 32768) {
      if (n = Gf(n, t), n !== null) {
        n.flags &= 32767, ne = n;
        return;
      }
      if (e !== null) e.flags |= 32768, e.subtreeFlags = 0, e.deletions = null;
      else {
        le = 6, ne = null;
        return;
      }
    } else if (n = Kf(n, t, Ce), n !== null) {
      ne = n;
      return;
    }
    if (t = t.sibling, t !== null) {
      ne = t;
      return;
    }
    ne = t = e;
  } while (t !== null);
  le === 0 && (le = 5);
}
function It(e, t, n) {
  var r = I, l = Re.transition;
  try {
    Re.transition = null, I = 1, tp(e, t, n, r);
  } finally {
    Re.transition = l, I = r;
  }
  return null;
}
function tp(e, t, n, r) {
  do
    wn();
  while (_t !== null);
  if (D & 6) throw Error(y(327));
  n = e.finishedWork;
  var l = e.finishedLanes;
  if (n === null) return null;
  if (e.finishedWork = null, e.finishedLanes = 0, n === e.current) throw Error(y(177));
  e.callbackNode = null, e.callbackPriority = 0;
  var i = n.lanes | n.childLanes;
  if (Ad(e, i), e === ue && (ne = ue = null, ae = 0), !(n.subtreeFlags & 2064) && !(n.flags & 2064) || $r || ($r = !0, Nc(il, function() {
    return wn(), null;
  })), i = (n.flags & 15990) !== 0, n.subtreeFlags & 15990 || i) {
    i = Re.transition, Re.transition = null;
    var o = I;
    I = 1;
    var u = D;
    D |= 4, Go.current = null, Xf(e, n), yc(n, e), Sf(Ii), ul = !!Wi, Ii = Wi = null, e.current = n, Zf(n), zd(), D = u, I = o, Re.transition = i;
  } else e.current = n;
  if ($r && ($r = !1, _t = e, _l = l), i = e.pendingLanes, i === 0 && (Pt = null), jd(n.stateNode), ze(e, ee()), t !== null) for (r = e.onRecoverableError, n = 0; n < t.length; n++) l = t[n], r(l.value, { componentStack: l.stack, digest: l.digest });
  if (xl) throw xl = !1, e = io, io = null, e;
  return _l & 1 && e.tag !== 0 && wn(), i = e.pendingLanes, i & 1 ? e === oo ? nr++ : (nr = 0, oo = e) : nr = 0, Ot(), null;
}
function wn() {
  if (_t !== null) {
    var e = la(_l), t = Re.transition, n = I;
    try {
      if (Re.transition = null, I = 16 > e ? 16 : e, _t === null) var r = !1;
      else {
        if (e = _t, _t = null, _l = 0, D & 6) throw Error(y(331));
        var l = D;
        for (D |= 4, C = e.current; C !== null; ) {
          var i = C, o = i.child;
          if (C.flags & 16) {
            var u = i.deletions;
            if (u !== null) {
              for (var s = 0; s < u.length; s++) {
                var c = u[s];
                for (C = c; C !== null; ) {
                  var m = C;
                  switch (m.tag) {
                    case 0:
                    case 11:
                    case 15:
                      er(8, m, i);
                  }
                  var h = m.child;
                  if (h !== null) h.return = m, C = h;
                  else for (; C !== null; ) {
                    m = C;
                    var g = m.sibling, k = m.return;
                    if (hc(m), m === c) {
                      C = null;
                      break;
                    }
                    if (g !== null) {
                      g.return = k, C = g;
                      break;
                    }
                    C = k;
                  }
                }
              }
              var x = i.alternate;
              if (x !== null) {
                var w = x.child;
                if (w !== null) {
                  x.child = null;
                  do {
                    var U = w.sibling;
                    w.sibling = null, w = U;
                  } while (w !== null);
                }
              }
              C = i;
            }
          }
          if (i.subtreeFlags & 2064 && o !== null) o.return = i, C = o;
          else e: for (; C !== null; ) {
            if (i = C, i.flags & 2048) switch (i.tag) {
              case 0:
              case 11:
              case 15:
                er(9, i, i.return);
            }
            var f = i.sibling;
            if (f !== null) {
              f.return = i.return, C = f;
              break e;
            }
            C = i.return;
          }
        }
        var a = e.current;
        for (C = a; C !== null; ) {
          o = C;
          var p = o.child;
          if (o.subtreeFlags & 2064 && p !== null) p.return = o, C = p;
          else e: for (o = a; C !== null; ) {
            if (u = C, u.flags & 2048) try {
              switch (u.tag) {
                case 0:
                case 11:
                case 15:
                  Rl(9, u);
              }
            } catch (E) {
              b(u, u.return, E);
            }
            if (u === o) {
              C = null;
              break e;
            }
            var v = u.sibling;
            if (v !== null) {
              v.return = u.return, C = v;
              break e;
            }
            C = u.return;
          }
        }
        if (D = l, Ot(), Je && typeof Je.onPostCommitFiberRoot == "function") try {
          Je.onPostCommitFiberRoot(Nl, e);
        } catch {
        }
        r = !0;
      }
      return r;
    } finally {
      I = n, Re.transition = t;
    }
  }
  return !1;
}
function fs(e, t, n) {
  t = Cn(n, t), t = lc(e, t, 1), e = jt(e, t, 1), t = ye(), e !== null && (Sr(e, 1, t), ze(e, t));
}
function b(e, t, n) {
  if (e.tag === 3) fs(e, e, n);
  else for (; t !== null; ) {
    if (t.tag === 3) {
      fs(t, e, n);
      break;
    } else if (t.tag === 1) {
      var r = t.stateNode;
      if (typeof t.type.getDerivedStateFromError == "function" || typeof r.componentDidCatch == "function" && (Pt === null || !Pt.has(r))) {
        e = Cn(n, e), e = ic(t, e, 1), t = jt(t, e, 1), e = ye(), t !== null && (Sr(t, 1, e), ze(t, e));
        break;
      }
    }
    t = t.return;
  }
}
function np(e, t, n) {
  var r = e.pingCache;
  r !== null && r.delete(t), t = ye(), e.pingedLanes |= e.suspendedLanes & n, ue === e && (ae & n) === n && (le === 4 || le === 3 && (ae & 130023424) === ae && 500 > ee() - Xo ? Vt(e, 0) : Yo |= n), ze(e, t);
}
function zc(e, t) {
  t === 0 && (e.mode & 1 ? (t = Lr, Lr <<= 1, !(Lr & 130023424) && (Lr = 4194304)) : t = 1);
  var n = ye();
  e = ct(e, t), e !== null && (Sr(e, t, n), ze(e, n));
}
function rp(e) {
  var t = e.memoizedState, n = 0;
  t !== null && (n = t.retryLane), zc(e, n);
}
function lp(e, t) {
  var n = 0;
  switch (e.tag) {
    case 13:
      var r = e.stateNode, l = e.memoizedState;
      l !== null && (n = l.retryLane);
      break;
    case 19:
      r = e.stateNode;
      break;
    default:
      throw Error(y(314));
  }
  r !== null && r.delete(t), zc(e, n);
}
var Cc;
Cc = function(e, t, n) {
  if (e !== null) if (e.memoizedProps !== t.pendingProps || _e.current) xe = !0;
  else {
    if (!(e.lanes & n) && !(t.flags & 128)) return xe = !1, Qf(e, t, n);
    xe = !!(e.flags & 131072);
  }
  else xe = !1, G && t.flags & 1048576 && Ta(t, ml, t.index);
  switch (t.lanes = 0, t.tag) {
    case 2:
      var r = t.type;
      qr(e, t), e = t.pendingProps;
      var l = xn(t, he.current);
      kn(t, n), l = Bo(null, t, r, e, l, n);
      var i = Vo();
      return t.flags |= 1, typeof l == "object" && l !== null && typeof l.render == "function" && l.$$typeof === void 0 ? (t.tag = 1, t.memoizedState = null, t.updateQueue = null, Ee(r) ? (i = !0, fl(t)) : i = !1, t.memoizedState = l.state !== null && l.state !== void 0 ? l.state : null, Do(t), l.updater = Al, t.stateNode = l, l._reactInternals = t, Yi(t, r, e, n), t = Ji(null, t, r, !0, i, n)) : (t.tag = 0, G && i && To(t), ve(null, t, l, n), t = t.child), t;
    case 16:
      r = t.elementType;
      e: {
        switch (qr(e, t), e = t.pendingProps, l = r._init, r = l(r._payload), t.type = r, l = t.tag = op(r), e = Ue(r, e), l) {
          case 0:
            t = Zi(null, t, r, e, n);
            break e;
          case 1:
            t = ns(null, t, r, e, n);
            break e;
          case 11:
            t = es(null, t, r, e, n);
            break e;
          case 14:
            t = ts(null, t, r, Ue(r.type, e), n);
            break e;
        }
        throw Error(y(
          306,
          r,
          ""
        ));
      }
      return t;
    case 0:
      return r = t.type, l = t.pendingProps, l = t.elementType === r ? l : Ue(r, l), Zi(e, t, r, l, n);
    case 1:
      return r = t.type, l = t.pendingProps, l = t.elementType === r ? l : Ue(r, l), ns(e, t, r, l, n);
    case 3:
      e: {
        if (ac(t), e === null) throw Error(y(387));
        r = t.pendingProps, i = t.memoizedState, l = i.element, Oa(e, t), vl(t, r, null, n);
        var o = t.memoizedState;
        if (r = o.element, i.isDehydrated) if (i = { element: r, isDehydrated: !1, cache: o.cache, pendingSuspenseBoundaries: o.pendingSuspenseBoundaries, transitions: o.transitions }, t.updateQueue.baseState = i, t.memoizedState = i, t.flags & 256) {
          l = Cn(Error(y(423)), t), t = rs(e, t, r, n, l);
          break e;
        } else if (r !== l) {
          l = Cn(Error(y(424)), t), t = rs(e, t, r, n, l);
          break e;
        } else for (Ne = Nt(t.stateNode.containerInfo.firstChild), je = t, G = !0, Be = null, n = Aa(t, null, r, n), t.child = n; n; ) n.flags = n.flags & -3 | 4096, n = n.sibling;
        else {
          if (_n(), r === l) {
            t = dt(e, t, n);
            break e;
          }
          ve(e, t, r, n);
        }
        t = t.child;
      }
      return t;
    case 5:
      return Da(t), e === null && Qi(t), r = t.type, l = t.pendingProps, i = e !== null ? e.memoizedProps : null, o = l.children, Ui(r, l) ? o = null : i !== null && Ui(r, i) && (t.flags |= 32), sc(e, t), ve(e, t, o, n), t.child;
    case 6:
      return e === null && Qi(t), null;
    case 13:
      return cc(e, t, n);
    case 4:
      return Wo(t, t.stateNode.containerInfo), r = t.pendingProps, e === null ? t.child = En(t, null, r, n) : ve(e, t, r, n), t.child;
    case 11:
      return r = t.type, l = t.pendingProps, l = t.elementType === r ? l : Ue(r, l), es(e, t, r, l, n);
    case 7:
      return ve(e, t, t.pendingProps, n), t.child;
    case 8:
      return ve(e, t, t.pendingProps.children, n), t.child;
    case 12:
      return ve(e, t, t.pendingProps.children, n), t.child;
    case 10:
      e: {
        if (r = t.type._context, l = t.pendingProps, i = t.memoizedProps, o = l.value, B(hl, r._currentValue), r._currentValue = o, i !== null) if (Qe(i.value, o)) {
          if (i.children === l.children && !_e.current) {
            t = dt(e, t, n);
            break e;
          }
        } else for (i = t.child, i !== null && (i.return = t); i !== null; ) {
          var u = i.dependencies;
          if (u !== null) {
            o = i.child;
            for (var s = u.firstContext; s !== null; ) {
              if (s.context === r) {
                if (i.tag === 1) {
                  s = ut(-1, n & -n), s.tag = 2;
                  var c = i.updateQueue;
                  if (c !== null) {
                    c = c.shared;
                    var m = c.pending;
                    m === null ? s.next = s : (s.next = m.next, m.next = s), c.pending = s;
                  }
                }
                i.lanes |= n, s = i.alternate, s !== null && (s.lanes |= n), Ki(
                  i.return,
                  n,
                  t
                ), u.lanes |= n;
                break;
              }
              s = s.next;
            }
          } else if (i.tag === 10) o = i.type === t.type ? null : i.child;
          else if (i.tag === 18) {
            if (o = i.return, o === null) throw Error(y(341));
            o.lanes |= n, u = o.alternate, u !== null && (u.lanes |= n), Ki(o, n, t), o = i.sibling;
          } else o = i.child;
          if (o !== null) o.return = i;
          else for (o = i; o !== null; ) {
            if (o === t) {
              o = null;
              break;
            }
            if (i = o.sibling, i !== null) {
              i.return = o.return, o = i;
              break;
            }
            o = o.return;
          }
          i = o;
        }
        ve(e, t, l.children, n), t = t.child;
      }
      return t;
    case 9:
      return l = t.type, r = t.pendingProps.children, kn(t, n), l = Oe(l), r = r(l), t.flags |= 1, ve(e, t, r, n), t.child;
    case 14:
      return r = t.type, l = Ue(r, t.pendingProps), l = Ue(r.type, l), ts(e, t, r, l, n);
    case 15:
      return oc(e, t, t.type, t.pendingProps, n);
    case 17:
      return r = t.type, l = t.pendingProps, l = t.elementType === r ? l : Ue(r, l), qr(e, t), t.tag = 1, Ee(r) ? (e = !0, fl(t)) : e = !1, kn(t, n), rc(t, r, l), Yi(t, r, l, n), Ji(null, t, r, !0, e, n);
    case 19:
      return dc(e, t, n);
    case 22:
      return uc(e, t, n);
  }
  throw Error(y(156, t.tag));
};
function Nc(e, t) {
  return ea(e, t);
}
function ip(e, t, n, r) {
  this.tag = e, this.key = n, this.sibling = this.child = this.return = this.stateNode = this.type = this.elementType = null, this.index = 0, this.ref = null, this.pendingProps = t, this.dependencies = this.memoizedState = this.updateQueue = this.memoizedProps = null, this.mode = r, this.subtreeFlags = this.flags = 0, this.deletions = null, this.childLanes = this.lanes = 0, this.alternate = null;
}
function Ae(e, t, n, r) {
  return new ip(e, t, n, r);
}
function bo(e) {
  return e = e.prototype, !(!e || !e.isReactComponent);
}
function op(e) {
  if (typeof e == "function") return bo(e) ? 1 : 0;
  if (e != null) {
    if (e = e.$$typeof, e === yo) return 11;
    if (e === ko) return 14;
  }
  return 2;
}
function Lt(e, t) {
  var n = e.alternate;
  return n === null ? (n = Ae(e.tag, t, e.key, e.mode), n.elementType = e.elementType, n.type = e.type, n.stateNode = e.stateNode, n.alternate = e, e.alternate = n) : (n.pendingProps = t, n.type = e.type, n.flags = 0, n.subtreeFlags = 0, n.deletions = null), n.flags = e.flags & 14680064, n.childLanes = e.childLanes, n.lanes = e.lanes, n.child = e.child, n.memoizedProps = e.memoizedProps, n.memoizedState = e.memoizedState, n.updateQueue = e.updateQueue, t = e.dependencies, n.dependencies = t === null ? null : { lanes: t.lanes, firstContext: t.firstContext }, n.sibling = e.sibling, n.index = e.index, n.ref = e.ref, n;
}
function tl(e, t, n, r, l, i) {
  var o = 2;
  if (r = e, typeof e == "function") bo(e) && (o = 1);
  else if (typeof e == "string") o = 5;
  else e: switch (e) {
    case rn:
      return Ht(n.children, l, i, t);
    case vo:
      o = 8, l |= 8;
      break;
    case yi:
      return e = Ae(12, n, t, l | 2), e.elementType = yi, e.lanes = i, e;
    case ki:
      return e = Ae(13, n, t, l), e.elementType = ki, e.lanes = i, e;
    case wi:
      return e = Ae(19, n, t, l), e.elementType = wi, e.lanes = i, e;
    case Ds:
      return Dl(n, l, i, t);
    default:
      if (typeof e == "object" && e !== null) switch (e.$$typeof) {
        case Rs:
          o = 10;
          break e;
        case Os:
          o = 9;
          break e;
        case yo:
          o = 11;
          break e;
        case ko:
          o = 14;
          break e;
        case yt:
          o = 16, r = null;
          break e;
      }
      throw Error(y(130, e == null ? e : typeof e, ""));
  }
  return t = Ae(o, n, t, l), t.elementType = e, t.type = r, t.lanes = i, t;
}
function Ht(e, t, n, r) {
  return e = Ae(7, e, r, t), e.lanes = n, e;
}
function Dl(e, t, n, r) {
  return e = Ae(22, e, r, t), e.elementType = Ds, e.lanes = n, e.stateNode = { isHidden: !1 }, e;
}
function mi(e, t, n) {
  return e = Ae(6, e, null, t), e.lanes = n, e;
}
function hi(e, t, n) {
  return t = Ae(4, e.children !== null ? e.children : [], e.key, t), t.lanes = n, t.stateNode = { containerInfo: e.containerInfo, pendingChildren: null, implementation: e.implementation }, t;
}
function up(e, t, n, r, l) {
  this.tag = t, this.containerInfo = e, this.finishedWork = this.pingCache = this.current = this.pendingChildren = null, this.timeoutHandle = -1, this.callbackNode = this.pendingContext = this.context = null, this.callbackPriority = 0, this.eventTimes = Xl(0), this.expirationTimes = Xl(-1), this.entangledLanes = this.finishedLanes = this.mutableReadLanes = this.expiredLanes = this.pingedLanes = this.suspendedLanes = this.pendingLanes = 0, this.entanglements = Xl(0), this.identifierPrefix = r, this.onRecoverableError = l, this.mutableSourceEagerHydrationData = null;
}
function eu(e, t, n, r, l, i, o, u, s) {
  return e = new up(e, t, n, u, s), t === 1 ? (t = 1, i === !0 && (t |= 8)) : t = 0, i = Ae(3, null, null, t), e.current = i, i.stateNode = e, i.memoizedState = { element: r, isDehydrated: n, cache: null, transitions: null, pendingSuspenseBoundaries: null }, Do(i), e;
}
function sp(e, t, n) {
  var r = 3 < arguments.length && arguments[3] !== void 0 ? arguments[3] : null;
  return { $$typeof: nn, key: r == null ? null : "" + r, children: e, containerInfo: t, implementation: n };
}
function jc(e) {
  if (!e) return Mt;
  e = e._reactInternals;
  e: {
    if (Jt(e) !== e || e.tag !== 1) throw Error(y(170));
    var t = e;
    do {
      switch (t.tag) {
        case 3:
          t = t.stateNode.context;
          break e;
        case 1:
          if (Ee(t.type)) {
            t = t.stateNode.__reactInternalMemoizedMergedChildContext;
            break e;
          }
      }
      t = t.return;
    } while (t !== null);
    throw Error(y(171));
  }
  if (e.tag === 1) {
    var n = e.type;
    if (Ee(n)) return ja(e, n, t);
  }
  return t;
}
function Pc(e, t, n, r, l, i, o, u, s) {
  return e = eu(n, r, !0, e, l, i, o, u, s), e.context = jc(null), n = e.current, r = ye(), l = Tt(n), i = ut(r, l), i.callback = t ?? null, jt(n, i, l), e.current.lanes = l, Sr(e, l, r), ze(e, r), e;
}
function Wl(e, t, n, r) {
  var l = t.current, i = ye(), o = Tt(l);
  return n = jc(n), t.context === null ? t.context = n : t.pendingContext = n, t = ut(i, o), t.payload = { element: e }, r = r === void 0 ? null : r, r !== null && (t.callback = r), e = jt(l, t, o), e !== null && (He(e, l, o, i), Xr(e, l, o)), o;
}
function zl(e) {
  if (e = e.current, !e.child) return null;
  switch (e.child.tag) {
    case 5:
      return e.child.stateNode;
    default:
      return e.child.stateNode;
  }
}
function ps(e, t) {
  if (e = e.memoizedState, e !== null && e.dehydrated !== null) {
    var n = e.retryLane;
    e.retryLane = n !== 0 && n < t ? n : t;
  }
}
function tu(e, t) {
  ps(e, t), (e = e.alternate) && ps(e, t);
}
function ap() {
  return null;
}
var Tc = typeof reportError == "function" ? reportError : function(e) {
  console.error(e);
};
function nu(e) {
  this._internalRoot = e;
}
Il.prototype.render = nu.prototype.render = function(e) {
  var t = this._internalRoot;
  if (t === null) throw Error(y(409));
  Wl(e, t, null, null);
};
Il.prototype.unmount = nu.prototype.unmount = function() {
  var e = this._internalRoot;
  if (e !== null) {
    this._internalRoot = null;
    var t = e.containerInfo;
    Xt(function() {
      Wl(null, e, null, null);
    }), t[at] = null;
  }
};
function Il(e) {
  this._internalRoot = e;
}
Il.prototype.unstable_scheduleHydration = function(e) {
  if (e) {
    var t = ua();
    e = { blockedOn: null, target: e, priority: t };
    for (var n = 0; n < wt.length && t !== 0 && t < wt[n].priority; n++) ;
    wt.splice(n, 0, e), n === 0 && aa(e);
  }
};
function ru(e) {
  return !(!e || e.nodeType !== 1 && e.nodeType !== 9 && e.nodeType !== 11);
}
function Ul(e) {
  return !(!e || e.nodeType !== 1 && e.nodeType !== 9 && e.nodeType !== 11 && (e.nodeType !== 8 || e.nodeValue !== " react-mount-point-unstable "));
}
function ms() {
}
function cp(e, t, n, r, l) {
  if (l) {
    if (typeof r == "function") {
      var i = r;
      r = function() {
        var c = zl(o);
        i.call(c);
      };
    }
    var o = Pc(t, r, e, 0, null, !1, !1, "", ms);
    return e._reactRootContainer = o, e[at] = o.current, dr(e.nodeType === 8 ? e.parentNode : e), Xt(), o;
  }
  for (; l = e.lastChild; ) e.removeChild(l);
  if (typeof r == "function") {
    var u = r;
    r = function() {
      var c = zl(s);
      u.call(c);
    };
  }
  var s = eu(e, 0, !1, null, null, !1, !1, "", ms);
  return e._reactRootContainer = s, e[at] = s.current, dr(e.nodeType === 8 ? e.parentNode : e), Xt(function() {
    Wl(t, s, n, r);
  }), s;
}
function $l(e, t, n, r, l) {
  var i = n._reactRootContainer;
  if (i) {
    var o = i;
    if (typeof l == "function") {
      var u = l;
      l = function() {
        var s = zl(o);
        u.call(s);
      };
    }
    Wl(t, o, e, l);
  } else o = cp(n, t, e, l, r);
  return zl(o);
}
ia = function(e) {
  switch (e.tag) {
    case 3:
      var t = e.stateNode;
      if (t.current.memoizedState.isDehydrated) {
        var n = Kn(t.pendingLanes);
        n !== 0 && (xo(t, n | 1), ze(t, ee()), !(D & 6) && (Nn = ee() + 500, Ot()));
      }
      break;
    case 13:
      Xt(function() {
        var r = ct(e, 1);
        if (r !== null) {
          var l = ye();
          He(r, e, 1, l);
        }
      }), tu(e, 1);
  }
};
_o = function(e) {
  if (e.tag === 13) {
    var t = ct(e, 134217728);
    if (t !== null) {
      var n = ye();
      He(t, e, 134217728, n);
    }
    tu(e, 134217728);
  }
};
oa = function(e) {
  if (e.tag === 13) {
    var t = Tt(e), n = ct(e, t);
    if (n !== null) {
      var r = ye();
      He(n, e, t, r);
    }
    tu(e, t);
  }
};
ua = function() {
  return I;
};
sa = function(e, t) {
  var n = I;
  try {
    return I = e, t();
  } finally {
    I = n;
  }
};
Ti = function(e, t, n) {
  switch (t) {
    case "input":
      if (_i(e, n), t = n.name, n.type === "radio" && t != null) {
        for (n = e; n.parentNode; ) n = n.parentNode;
        for (n = n.querySelectorAll("input[name=" + JSON.stringify("" + t) + '][type="radio"]'), t = 0; t < n.length; t++) {
          var r = n[t];
          if (r !== e && r.form === e.form) {
            var l = Ll(r);
            if (!l) throw Error(y(90));
            Is(r), _i(r, l);
          }
        }
      }
      break;
    case "textarea":
      $s(e, n);
      break;
    case "select":
      t = n.value, t != null && hn(e, !!n.multiple, t, !1);
  }
};
Ys = Zo;
Xs = Xt;
var dp = { usingClientEntryPoint: !1, Events: [_r, sn, Ll, Ks, Gs, Zo] }, Bn = { findFiberByHostInstance: Ut, bundleType: 0, version: "18.3.1", rendererPackageName: "react-dom" }, fp = { bundleType: Bn.bundleType, version: Bn.version, rendererPackageName: Bn.rendererPackageName, rendererConfig: Bn.rendererConfig, overrideHookState: null, overrideHookStateDeletePath: null, overrideHookStateRenamePath: null, overrideProps: null, overridePropsDeletePath: null, overridePropsRenamePath: null, setErrorHandler: null, setSuspenseHandler: null, scheduleUpdate: null, currentDispatcherRef: ft.ReactCurrentDispatcher, findHostInstanceByFiber: function(e) {
  return e = qs(e), e === null ? null : e.stateNode;
}, findFiberByHostInstance: Bn.findFiberByHostInstance || ap, findHostInstancesForRefresh: null, scheduleRefresh: null, scheduleRoot: null, setRefreshHandler: null, getCurrentFiber: null, reconcilerVersion: "18.3.1-next-f1338f8080-20240426" };
if (typeof __REACT_DEVTOOLS_GLOBAL_HOOK__ < "u") {
  var Br = __REACT_DEVTOOLS_GLOBAL_HOOK__;
  if (!Br.isDisabled && Br.supportsFiber) try {
    Nl = Br.inject(fp), Je = Br;
  } catch {
  }
}
Te.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED = dp;
Te.createPortal = function(e, t) {
  var n = 2 < arguments.length && arguments[2] !== void 0 ? arguments[2] : null;
  if (!ru(t)) throw Error(y(200));
  return sp(e, t, null, n);
};
Te.createRoot = function(e, t) {
  if (!ru(e)) throw Error(y(299));
  var n = !1, r = "", l = Tc;
  return t != null && (t.unstable_strictMode === !0 && (n = !0), t.identifierPrefix !== void 0 && (r = t.identifierPrefix), t.onRecoverableError !== void 0 && (l = t.onRecoverableError)), t = eu(e, 1, !1, null, null, n, !1, r, l), e[at] = t.current, dr(e.nodeType === 8 ? e.parentNode : e), new nu(t);
};
Te.findDOMNode = function(e) {
  if (e == null) return null;
  if (e.nodeType === 1) return e;
  var t = e._reactInternals;
  if (t === void 0)
    throw typeof e.render == "function" ? Error(y(188)) : (e = Object.keys(e).join(","), Error(y(268, e)));
  return e = qs(t), e = e === null ? null : e.stateNode, e;
};
Te.flushSync = function(e) {
  return Xt(e);
};
Te.hydrate = function(e, t, n) {
  if (!Ul(t)) throw Error(y(200));
  return $l(null, e, t, !0, n);
};
Te.hydrateRoot = function(e, t, n) {
  if (!ru(e)) throw Error(y(405));
  var r = n != null && n.hydratedSources || null, l = !1, i = "", o = Tc;
  if (n != null && (n.unstable_strictMode === !0 && (l = !0), n.identifierPrefix !== void 0 && (i = n.identifierPrefix), n.onRecoverableError !== void 0 && (o = n.onRecoverableError)), t = Pc(t, null, e, 1, n ?? null, l, !1, i, o), e[at] = t.current, dr(e), r) for (e = 0; e < r.length; e++) n = r[e], l = n._getVersion, l = l(n._source), t.mutableSourceEagerHydrationData == null ? t.mutableSourceEagerHydrationData = [n, l] : t.mutableSourceEagerHydrationData.push(
    n,
    l
  );
  return new Il(t);
};
Te.render = function(e, t, n) {
  if (!Ul(t)) throw Error(y(200));
  return $l(null, e, t, !1, n);
};
Te.unmountComponentAtNode = function(e) {
  if (!Ul(e)) throw Error(y(40));
  return e._reactRootContainer ? (Xt(function() {
    $l(null, null, e, !1, function() {
      e._reactRootContainer = null, e[at] = null;
    });
  }), !0) : !1;
};
Te.unstable_batchedUpdates = Zo;
Te.unstable_renderSubtreeIntoContainer = function(e, t, n, r) {
  if (!Ul(n)) throw Error(y(200));
  if (e == null || e._reactInternals === void 0) throw Error(y(38));
  return $l(e, t, n, !1, r);
};
Te.version = "18.3.1-next-f1338f8080-20240426";
function Lc() {
  if (!(typeof __REACT_DEVTOOLS_GLOBAL_HOOK__ > "u" || typeof __REACT_DEVTOOLS_GLOBAL_HOOK__.checkDCE != "function"))
    try {
      __REACT_DEVTOOLS_GLOBAL_HOOK__.checkDCE(Lc);
    } catch (e) {
      console.error(e);
    }
}
Lc(), Ls.exports = Te;
var pp = Ls.exports, Fc, hs = pp;
Fc = hs.createRoot, hs.hydrateRoot;
function lu(e) {
  return e.replace(/\/+$/, "");
}
async function iu(e, t) {
  let n = "";
  try {
    n = await e.text();
  } catch {
    n = "";
  }
  const r = new Error(`${t} failed with ${e.status}${n ? `: ${n.slice(0, 500)}` : ""}`);
  return r.status = e.status, r.body = n, r;
}
function Ze(e) {
  return e && typeof e == "object" && !Array.isArray(e) ? e : {};
}
function mp(e) {
  const t = Ze(e), n = [
    t.config,
    t.client,
    t.data,
    t.result,
    t.item,
    t
  ];
  for (const r of n) {
    const l = Ze(r);
    if (Object.keys(l).length > 0) return l;
  }
  return t;
}
function $(e, t = "") {
  return e == null ? t : String(e);
}
function Mc(e, t = 0) {
  const n = Number(e);
  return Number.isFinite(n) ? n : t;
}
function hp(e, t = !1) {
  return typeof e == "boolean" ? e : typeof e == "number" ? e === 1 : typeof e == "string" ? ["1", "true", "yes", "ja", "on"].includes(e.toLowerCase()) : t;
}
function jn(e) {
  if (Array.isArray(e)) return e;
  if (typeof e == "string") {
    const t = e.trim();
    if (!t) return [];
    try {
      const n = JSON.parse(t), r = jn(n);
      if (r.length > 0) return r;
    } catch {
    }
    return t.split(/[;,\n]/).map((n) => n.trim()).filter(Boolean);
  }
  if (e && typeof e == "object") {
    const t = e, n = Object.keys(t).filter((r) => /^\d+$/.test(r));
    if (n.length > 0)
      return n.sort((r, l) => Number(r) - Number(l)).map((r) => t[r]).filter((r) => r != null && String(r).trim() !== "");
    for (const r of ["items", "data", "values", "list", "results"]) {
      const l = jn(t[r]);
      if (l.length > 0) return l;
    }
  }
  return [];
}
function Vn(e, t) {
  for (const n of t) {
    const r = jn(e[n]);
    if (r.length > 0) return r;
  }
  return [];
}
function gp(e) {
  const t = [];
  for (const n of jn(e)) {
    const r = Ze(n), l = Mc(r.id, NaN), i = $(r.name ?? r.title, "");
    !Number.isFinite(l) || !i || t.push({
      id: l,
      name: i,
      slug: typeof r.slug == "string" ? r.slug : void 0,
      icon: typeof r.icon == "string" ? r.icon : void 0
    });
  }
  return t;
}
async function vp(e, t) {
  if (!e) {
    const u = new Error("No apiBaseUrl configured");
    throw u.status = 0, u;
  }
  const n = `${lu(e)}/api/tfd-partner-wizard/config/${encodeURIComponent(t)}`, r = await fetch(n, {
    method: "GET",
    credentials: "omit",
    headers: {
      Accept: "application/json"
    }
  });
  if (!r.ok)
    throw await iu(r, "Config request");
  const l = await r.json(), i = mp(l), o = $(i.intro ?? i.introText ?? i.description, "Finde heraus, welches Partner-Modell und welche Tool-Richtung zu dir passt.");
  return {
    clientId: $(i.clientId ?? i.client_id ?? t, t),
    name: $(i.name, ""),
    headline: $(i.headline ?? i.title, "Chiptuning Händler werden"),
    intro: o,
    introText: o,
    primaryColor: $(i.primaryColor ?? i.primary_color, "#E31E24"),
    theme: $(i.theme, "light"),
    toolRequired: hp(i.toolRequired ?? i.tool_required, !0),
    defaultVehiclePrice: Mc(i.defaultVehiclePrice ?? i.default_vehicle_price, 0),
    enabledVehicleCategories: gp(
      Vn(i, ["enabledVehicleCategories", "enabled_vehicle_categories", "vehicleCategories", "vehicle_categories", "vehicles"])
    ),
    enabledServices: Vn(i, ["enabledServices", "enabled_services", "services"]),
    currentToolOptions: Vn(i, ["currentToolOptions", "current_tool_options", "toolOptions", "tool_options", "currentTools", "current_tools"]),
    customLabels: i.customLabels ?? i.custom_labels ?? {},
    privacyNotice: $(
      i.privacyNotice ?? i.privacy_notice,
      "Mit dem Absenden erklärst du dich einverstanden, dass deine Angaben zur Bearbeitung der Anfrage gespeichert werden und wir dich kontaktieren."
    ),
    toolDisclaimer: $(i.toolDisclaimer ?? i.tool_disclaimer, ""),
    ctaLabel: $(i.ctaLabel ?? i.cta_label, "Anfrage senden"),
    contactTarget: $(i.contactTarget ?? i.contact_target, ""),
    tools: Vn(i, ["tools", "toolRecommendations", "tool_recommendations", "recommendedTools"]),
    packages: Vn(i, ["packages", "partnerPackages", "partner_packages"]),
    raw: l
  };
}
async function yp(e, t) {
  if (!e)
    throw new Error("No apiBaseUrl configured");
  const n = `${lu(e)}/api/tfd-partner-wizard/recommend`, r = await fetch(n, {
    method: "POST",
    credentials: "omit",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json"
    },
    body: JSON.stringify(t)
  });
  if (!r.ok)
    throw await iu(r, "Recommendation request");
  const l = await r.json();
  return _p(l);
}
async function kp(e, t) {
  if (!e)
    throw new Error("No apiBaseUrl configured");
  const n = `${lu(e)}/api/tfd-partner-wizard/leads`, r = await fetch(n, {
    method: "POST",
    credentials: "omit",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json"
    },
    body: JSON.stringify(t)
  });
  if (!r.ok)
    throw await iu(r, "Lead request");
  try {
    return await r.json();
  } catch {
    return null;
  }
}
function wp(e) {
  return jn(e).map((t) => {
    if (typeof t == "string") return t.trim();
    const n = Ze(t);
    return $(n.name ?? n.title ?? n.label ?? n.text ?? n.description).trim();
  }).filter(Boolean);
}
function Sp(e) {
  const t = Ze(e), n = $(t.title).trim();
  if (n)
    return {
      uid: typeof t.uid == "number" ? t.uid : void 0,
      title: n,
      sku: typeof t.sku == "string" ? t.sku : void 0,
      price: t.price !== void 0 && t.price !== null ? String(t.price) : void 0,
      slug: typeof t.slug == "string" ? t.slug : void 0,
      imageUrl: $(t.imageUrl ?? t.image_url).trim() || void 0
    };
}
function xp(e) {
  const t = [];
  return jn(e).forEach((n, r) => {
    if (typeof n == "string") {
      const s = n.trim();
      s && t.push({ key: `tool-${r}`, name: s });
      return;
    }
    const l = Ze(n), i = $(l.name ?? l.title ?? l.label).trim();
    if (!i) return;
    const o = Sp(l.product), u = $(l.imageUrl ?? l.image_url).trim() || (o == null ? void 0 : o.imageUrl);
    t.push({
      key: $(l.key ?? l.toolKey ?? `tool-${r}`),
      name: i,
      description: $(l.description ?? l.text, "").trim() || void 0,
      level: typeof l.level == "string" ? l.level : void 0,
      imageUrl: u,
      product: o,
      fromCombination: l.fromCombination === !0
    });
  }), t;
}
function _p(e) {
  const t = Ze(e), n = Ze(t.recommendation), r = Ze(t.result), l = Object.keys(n).length > 0 ? n : Object.keys(r).length > 0 ? r : t, i = Ze(l.package ?? l.recommendedPackage ?? l.packageData ?? l.recommended_package), o = l.packageName ?? l.packageTitle ?? l.package_title ?? l.recommendedPackageTitle ?? l.recommended_package_title ?? l.title ?? l.name ?? i.name ?? i.title ?? i.packageTitle ?? "Partner Empfehlung", u = l.packageKey ?? l.package_key ?? l.key ?? l.slug ?? i.key ?? i.slug ?? $(o).toLowerCase().replace(/[^a-z0-9]+/gi, "-"), s = wp(
    l.bullets ?? l.items ?? l.benefits ?? l.packageFeatures ?? l.package_features ?? l.features ?? i.bullets ?? i.benefits ?? i.features ?? []
  ), c = xp(
    l.recommendedTools ?? l.recommended_tools ?? l.tools ?? l.toolRecommendations ?? l.tool_recommendations ?? []
  ), h = $(l.status ?? l.recommendationStatus ?? l.recommendation_status, "ready") === "tool_required" || l.toolRequired === !0 ? "tool_required" : "ready";
  return {
    status: h,
    packageKey: $(u),
    packageName: $(o),
    leadStatus: h === "tool_required" ? "tool_consulting" : "new_partner_lead",
    cta: $(
      l.cta ?? l.ctaLabel ?? l.cta_label ?? l.nextStep ?? l.next_step ?? i.ctaLabel,
      h === "tool_required" ? "Tool-Beratung anfragen" : "Anfrage senden"
    ),
    summary: $(
      l.summary ?? l.packageText ?? l.package_text ?? l.shortDescription ?? l.description ?? i.shortDescription ?? i.description,
      "Auf Basis deiner Angaben bereiten wir die passende Empfehlung vor."
    ),
    bullets: s,
    recommendedTools: c,
    toolDirection: $(l.toolDirection ?? l.tool_direction, ""),
    disclaimer: $(l.disclaimer ?? l.toolDisclaimer ?? l.tool_disclaimer, ""),
    raw: e
  };
}
const gi = {
  clientId: "no1",
  headline: "Chiptuning Händler werden",
  intro: "Finde heraus, welches Partner-Modell und welche Tool-Richtung zu dir passt.",
  privacyNotice: "Mit dem Absenden erklärst du dich einverstanden, dass deine Angaben zur Bearbeitung deiner Anfrage gespeichert werden.",
  ctaLabel: "Anfrage senden"
}, Ep = [
  {
    value: "start",
    title: "Neu starten",
    text: "Ich möchte in das Thema Chiptuning einsteigen."
  },
  {
    value: "workshop",
    title: "Werkstatt erweitern",
    text: "Ich habe Kundenkontakt und möchte Chiptuning anbieten."
  },
  {
    value: "active",
    title: "Aktiver Tuner",
    text: "Ich möchte regelmäßig Fahrzeuge bearbeiten."
  },
  {
    value: "network",
    title: "Händlernetz / Master",
    text: "Ich denke an Master, Slaves oder White-Label."
  }
], zp = [
  {
    value: "none",
    title: "Keine Erfahrung",
    text: "Ich brauche einen geführten Einstieg."
  },
  {
    value: "diagnosis",
    title: "Diagnose / Codierung",
    text: "Ich arbeite schon am Fahrzeug."
  },
  {
    value: "workshop",
    title: "Werkstatt-Erfahrung",
    text: "Ich habe regelmäßig Kundenfahrzeuge."
  },
  {
    value: "tuning",
    title: "Chiptuning-Erfahrung",
    text: "Ich lese/schreibe bereits Steuergeräte."
  }
], Cp = [
  { value: "pkw", title: "PKW", text: "Standard-Fahrzeuge, Performance und Daily Driver." },
  { value: "lkw", title: "LKW", text: "Nutzfahrzeuge, Flotten und Speditionen." },
  { value: "motorrad", title: "Motorrad", text: "Bike-Tuning und Performance-Anwendungen." },
  { value: "agrar", title: "Agrar", text: "Traktoren, Landmaschinen und Eco-Anwendungen." },
  { value: "atv", title: "Quad", text: "ATV, Quad und Side-by-Side." },
  { value: "boat", title: "Boot", text: "Boote und Marine-Anwendungen." },
  { value: "jetski", title: "Jetski", text: "JetSki und Wassersport-Fahrzeuge." }
], gs = [
  {
    value: "none",
    title: "Noch kein Tool",
    text: "Ich brauche zuerst eine Tool-Beratung."
  },
  {
    value: "autotuner",
    title: "Autotuner",
    text: "Autotuner Master oder Slave vorhanden."
  },
  {
    value: "kess3",
    title: "Alientech KESS3",
    text: "KESS3 Master oder Slave vorhanden."
  },
  {
    value: "flex",
    title: "Magic Motorsport FLEX",
    text: "FLEX Tool vorhanden."
  },
  {
    value: "cmd",
    title: "CMD Flash",
    text: "CMD Tool vorhanden."
  },
  {
    value: "dimsport",
    title: "Dimsport",
    text: "New Genius / Trasdata oder Dimsport vorhanden."
  },
  {
    value: "autoflasher",
    title: "AutoFlasher",
    text: "AutoFlasher Tool vorhanden."
  },
  {
    value: "other",
    title: "Anderes Tool",
    text: "Ich nutze ein anderes Lese-/Schreibtool."
  }
], Np = [
  { value: "stage1", title: "Stage 1", text: "Leistungsoptimierung für Kundenfahrzeuge." },
  { value: "stage2", title: "Stage 2", text: "Optimierung mit Hardware-Anpassungen." },
  { value: "eco", title: "Eco-Tuning", text: "Verbrauchsoptimierung für Flotten und Gewerbe." },
  { value: "fileService", title: "File Service", text: "Files lesen, prüfen lassen und schreiben." },
  { value: "diagnostic", title: "Diagnose Support", text: "Technische Rückfragen, Fehlerbilder und Fälle." },
  { value: "special", title: "Sonderoptionen", text: "VMax, DSG, Start-Stop und weitere Optionen." }
], jp = [
  { value: "low", title: "1–5 Fahrzeuge / Monat", text: "Kleiner Start oder gelegentliche Anfragen." },
  { value: "medium", title: "6–20 Fahrzeuge / Monat", text: "Regelmäßiges Geschäft mit Wachstum." },
  { value: "high", title: "20+ Fahrzeuge / Monat", text: "Professioneller Betrieb mit Volumen." },
  { value: "network", title: "Händler / Netzwerk", text: "Mehrere Standorte, Slaves oder Partner." }
];
function Pp(e, t) {
  return e.tool === "none" ? {
    status: "tool_required",
    packageName: "Tool Starter",
    leadStatus: "tool_consulting",
    cta: "Tool-Beratung anfragen",
    summary: "Für den Händlerstart wird zuerst ein passendes Lese-/Schreibtool benötigt. Danach kann die Partner-Aktivierung vorbereitet werden.",
    bullets: [
      "Tool-Beratung nach Fahrzeugbereich",
      "Prüfung der passenden Protokolle",
      "Startplan für den Händlerzugang"
    ]
  } : e.goal === "network" || e.volume === "network" ? {
    status: "ready",
    packageName: "Master / Netzwerk Partner",
    leadStatus: "new_partner_lead",
    cta: "Partnerstart anfragen",
    summary: "Du passt in Richtung Master, Händlernetz oder White-Label-Struktur mit eigenem Tool und skalierbaren Abläufen.",
    bullets: [
      "Partnerzugang mit Netzwerk-Fokus",
      "Skalierbare File-Service-Prozesse",
      "Technische Abstimmung für Slaves und Abläufe"
    ]
  } : e.volume === "high" || e.experience === "tuning" ? {
    status: "ready",
    packageName: "Profi Partner",
    leadStatus: "new_partner_lead",
    cta: "Partnerstart anfragen",
    summary: "Du hast bereits die richtige Basis für regelmäßige Fahrzeuge und einen professionellen Partner-Workflow.",
    bullets: [
      "Händlerkonditionen",
      "Schnelle Bearbeitung",
      "Technischer Support"
    ]
  } : {
    status: "ready",
    packageName: "Werkstatt / Slave Partner",
    leadStatus: "new_partner_lead",
    cta: "Partnerstart anfragen",
    summary: "Du kannst mit eigenem Tool und klarer Fahrzeugausrichtung als Werkstatt- oder Slave-Partner starten.",
    bullets: [
      "Eigener Händlerzugang",
      "File-Service für Kundenfahrzeuge",
      "Geführter Einstieg in den Ablauf"
    ]
  };
}
const Tp = {
  stage1: "Stage 1",
  stage2: "Stage 2",
  eco: "Eco Tuning",
  agr: "AGR OFF",
  egr: "EGR OFF",
  dpf: "DPF OFF",
  adblue: "AdBlue OFF",
  vmax: "VMax OFF",
  pops: "Pops & Bangs",
  hardcut: "Hardcut",
  launch: "Launch Control",
  dsg: "DSG / Getriebe",
  getriebe: "Getriebe"
}, Lp = {
  pkw: "Normale Kundenfahrzeuge und Performance-Umbauten.",
  lkw: "Truck- und Nutzfahrzeug-Anfragen.",
  agrar: "Traktoren und Agrartechnik.",
  motorrad: "Motorräder und Zweiräder.",
  atv: "ATV, Quad und Side-by-Side.",
  boat: "Boote und Marine-Anwendungen.",
  jetski: "JetSki und Wassersport-Fahrzeuge."
}, Fp = {
  stage1: "Leistungsoptimierung für Kundenfahrzeuge.",
  stage2: "Erweiterte Optimierungen und Umbauten.",
  eco: "Verbrauchsoptimierte Abstimmung.",
  agr: "AGR-bezogene Anfragen.",
  egr: "EGR-bezogene Anfragen.",
  dpf: "DPF-bezogene Anfragen.",
  adblue: "SCR/AdBlue-bezogene Anfragen.",
  vmax: "VMax-bezogene Anfragen.",
  pops: "Sound- und Motorsport-Features.",
  hardcut: "Hardcut / Drehzahlbegrenzung.",
  launch: "Launch-Control-Anfragen.",
  dsg: "Getriebe- und DSG-Anpassungen.",
  getriebe: "Getriebe- und Schaltlogik-Anfragen."
};
function vs(e, t, n) {
  const r = e.customLabels;
  if (r && typeof r == "object" && !Array.isArray(r)) {
    const l = r, i = l[t] ?? l[t.toLowerCase()] ?? l[n];
    if (typeof i == "string" && i.trim()) return i.trim();
  }
  return n;
}
function Mp(e) {
  if (typeof e == "string") return e;
  if (e && typeof e == "object") {
    const t = e, n = t.key ?? t.value ?? t.slug ?? t.name ?? t.title ?? t.label;
    return typeof n == "string" ? n : String(n ?? "");
  }
  return "";
}
function Ap(e, t) {
  if (!Array.isArray(e) || e.length === 0) return t;
  const n = [];
  for (const r of e) {
    if (!r.name.trim()) continue;
    const l = (r.slug ?? String(r.id)).toLowerCase();
    n.push({
      value: l,
      id: r.id,
      title: r.name,
      text: Lp[l] ?? "Aus deinen Admin-Einstellungen aktiviert.",
      icon: r.icon
    });
  }
  return n.length > 0 ? n : t;
}
function Ac(e, t, n, r, l) {
  if (!Array.isArray(e) || e.length === 0) return t;
  const i = e.map((o) => {
    if (o && typeof o == "object") {
      const h = o, g = String(h.key ?? h.value ?? h.slug ?? h.name ?? h.title ?? "").trim();
      if (!g) return null;
      const k = g.toLowerCase(), x = String(h.title ?? h.label ?? h.name ?? vs(l, k, n[k] ?? g)).trim(), w = String(h.text ?? h.description ?? r[k] ?? "Aus deinen Admin-Einstellungen aktiviert.").trim(), U = typeof h.icon == "string" && h.icon.trim() ? h.icon.trim() : void 0, f = typeof h.imageUrl == "string" && h.imageUrl.trim() ? h.imageUrl.trim() : typeof h.image_url == "string" && h.image_url.trim() ? h.image_url.trim() : void 0;
      return { value: k, title: x, text: w, icon: U, imageUrl: f };
    }
    const u = Mp(o).trim();
    if (!u) return null;
    const s = u.toLowerCase(), c = vs(l, s, n[s] ?? u), m = r[s] ?? "Aus deinen Admin-Einstellungen aktiviert.";
    return { value: s, title: c, text: m };
  }).filter(Boolean);
  return i.length > 0 ? i : t;
}
function Rp(e) {
  const t = gs[0], n = Ac(
    e.currentToolOptions,
    [],
    {},
    {},
    e
  ).filter((r) => r.value !== "none");
  return n.length > 0 ? [t, ...n] : gs;
}
const Vr = [
  { key: "entry", label: "Einstieg" },
  { key: "vehicleTool", label: "Fahrzeug & Tool" },
  { key: "offerVolume", label: "Angebot & Volumen" },
  { key: "recommendation", label: "Empfehlung" },
  { key: "contact", label: "Kontakt" }
], Op = [
  "Fahrzeugdatenbank API für 1 Jahr mit drin"
];
function ys(e, t) {
  const n = e ?? [];
  return n.includes(t) ? n.filter((r) => r !== t) : [...n, t];
}
function Dp(e) {
  var t;
  return !!((t = e.vehicles) != null && t.length && e.tool);
}
function tn(e) {
  if (!e) return;
  const t = Number(e);
  if (Number.isFinite(t))
    return `${t.toLocaleString("de-DE", { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €`;
}
function Wp(e) {
  switch (e) {
    case "low":
      return 3;
    case "medium":
      return 12;
    case "high":
      return 25;
    case "network":
      return 40;
    default:
      return 0;
  }
}
function Ip(e) {
  return !e || e === "none" || e === "other" ? "unknown" : "slave";
}
function ks(e, t, n, r) {
  const l = t.contact ?? {}, i = t.vehicles ?? [], o = i.map((h) => n[h]).filter((h) => typeof h == "number"), u = t.services ?? [], s = t.tool ?? "", c = Wp(t.volume), m = Ip(t.tool);
  return {
    clientId: e,
    contact: l,
    answers: t,
    recommendation: r,
    source: "no1_partner_wizard",
    selectedTools: t.selectedTools ?? [],
    // New Symfony recommendation service fields (VehicleType IDs, not slugs).
    currentTool: s,
    vehicleCategories: o,
    mainVehicleCategory: o[0] ?? "",
    services: u,
    goal: t.goal ?? "",
    experience: t.experience ?? "",
    monthlyVehicleCount: c,
    desiredToolLevel: m,
    // Lead / compatibility fields.
    packageName: r == null ? void 0 : r.packageName,
    packageKey: r == null ? void 0 : r.packageKey,
    leadStatus: r == null ? void 0 : r.leadStatus,
    status: r == null ? void 0 : r.status,
    name: l.name,
    company: l.company,
    email: l.email,
    phone: l.phone,
    message: l.message,
    privacyAccepted: t.privacyAccepted === !0,
    vehicles: i,
    tool: s,
    volume: t.volume,
    payload: {
      clientId: e,
      contact: l,
      answers: t,
      recommendation: r,
      currentTool: s,
      vehicleCategories: o,
      mainVehicleCategory: o[0] ?? "",
      services: u,
      monthlyVehicleCount: c,
      desiredToolLevel: m,
      privacyAccepted: t.privacyAccepted === !0,
      source: "no1_partner_wizard"
    }
  };
}
function Up({
  option: e,
  selected: t,
  onClick: n
}) {
  const r = e.imageUrl ?? e.icon;
  return /* @__PURE__ */ d.jsxs(
    "button",
    {
      type: "button",
      className: [
        "tfdWizard__option",
        r ? "tfdWizard__option--withIcon" : "",
        t ? "selected" : ""
      ].filter(Boolean).join(" "),
      onClick: n,
      "data-testid": `option-${e.value}`,
      children: [
        r && /* @__PURE__ */ d.jsx("img", { className: "tfdWizard__optionIcon", src: r, alt: "", loading: "lazy", "aria-hidden": "true" }),
        /* @__PURE__ */ d.jsxs("div", { className: "tfdWizard__optionBody", children: [
          /* @__PURE__ */ d.jsx("strong", { children: e.title }),
          /* @__PURE__ */ d.jsx("span", { children: e.text })
        ] })
      ]
    }
  );
}
function $p({
  clientId: e = "no1",
  apiBaseUrl: t = "",
  primaryColor: n = "#E31E24",
  theme: r = "light"
}) {
  const [l, i] = X.useState(gi), [o, u] = X.useState(""), [s, c] = X.useState(0), [m, h] = X.useState({}), [g, k] = X.useState(!1), [x, w] = X.useState(""), [U, f] = X.useState(null), [a, p] = X.useState(""), [v, E] = X.useState(!1), [_, N] = X.useState(null);
  X.useEffect(() => {
    let S = !0;
    return vp(t, e).then((P) => {
      S && (i({ ...gi, ...P }), u(""));
    }).catch((P) => {
      if (!S) return;
      i(gi);
      const O = typeof P == "object" && P !== null && "status" in P ? String(P.status ?? "offline") : "offline";
      u(`Config konnte nicht geladen werden (${O}). Lokale Fallback-Konfiguration aktiv.`);
    }), () => {
      S = !1;
    };
  }, [t, e]);
  const j = X.useMemo(
    () => Pp(m),
    [m, l]
  ), W = X.useMemo(
    () => Ap(l.enabledVehicleCategories, Cp),
    [l]
  ), M = X.useMemo(
    () => Object.fromEntries(
      W.filter((S) => typeof S.id == "number").map((S) => [S.value, S.id])
    ),
    [W]
  );
  X.useEffect(() => {
    if (!Dp(m)) {
      f(null), p("");
      return;
    }
    let S = !0;
    E(!0), p("");
    const P = ks(e, m, M, j);
    return yp(t, P).then((O) => {
      S && (f(O), p(""));
    }).catch((O) => {
      if (!S) return;
      f(null);
      const H = O instanceof Error ? O.message : "Recommendation API nicht erreichbar.";
      p(`${H} Lokale Empfehlung aktiv.`);
    }).finally(() => {
      S && E(!1);
    }), () => {
      S = !1;
    };
  }, [m, t, e, j, M]);
  const te = Vr[s], ie = U ?? j, pt = X.useMemo(
    () => (ie.recommendedTools ?? []).map((S) => S.key),
    [ie]
  ), Fn = X.useMemo(
    () => Ac(l.enabledServices, Np, Tp, Fp, l),
    [l]
  ), zr = X.useMemo(
    () => Rp(l),
    [l]
  ), Mn = Math.round((s + 1) / Vr.length * 100);
  X.useEffect(() => {
    if (!_) return;
    const S = (P) => {
      P.key === "Escape" && N(null);
    };
    return window.addEventListener("keydown", S), () => window.removeEventListener("keydown", S);
  }, [_]), X.useEffect(() => {
    h((S) => {
      const P = S.selectedTools ?? [];
      if (P.length === 0) return S;
      const O = P.filter((H) => pt.includes(H));
      return O.length === P.length ? S : { ...S, selectedTools: O };
    });
  }, [pt]);
  const mt = X.useMemo(() => {
    var S, P, O, H;
    switch (te.key) {
      case "entry":
        return !!(m.goal && m.experience);
      case "vehicleTool":
        return !!((S = m.vehicles) != null && S.length && m.tool);
      case "offerVolume":
        return !!((P = m.services) != null && P.length && m.volume);
      case "recommendation":
        return !0;
      case "contact":
        return !!((O = m.contact) != null && O.name && ((H = m.contact) != null && H.email) && m.privacyAccepted);
      default:
        return !0;
    }
  }, [m, te.key]), z = (S, P) => {
    h((O) => ({ ...O, [S]: P })), w("");
  }, T = (S, P) => {
    h((O) => ({
      ...O,
      [S]: ys(O[S], P)
    })), w("");
  }, F = W.length > 0 && W.every((S) => (m.vehicles ?? []).includes(S.value)), Y = () => {
    h((S) => ({
      ...S,
      vehicles: F ? [] : W.map((P) => P.value)
    })), w("");
  }, V = (S, P, O = !1) => /* @__PURE__ */ d.jsx("div", { className: "tfdWizard__grid", children: S.map((H) => {
    const We = m[P], ht = Array.isArray(We) ? We.includes(H.value) : We === H.value;
    return /* @__PURE__ */ d.jsx(
      Up,
      {
        option: H,
        selected: ht,
        onClick: () => {
          O && (P === "vehicles" || P === "services") ? T(P, H.value) : z(P, H.value);
        }
      },
      H.value
    );
  }) }), qt = () => {
    mt && (c((S) => Math.min(S + 1, Vr.length - 1)), w(""));
  }, be = () => {
    c((S) => Math.max(S - 1, 0)), w("");
  }, et = (S, P) => {
    h((O) => ({
      ...O,
      contact: {
        ...O.contact,
        [S]: P
      }
    })), w("");
  }, tt = (S) => {
    h((P) => ({ ...P, privacyAccepted: S })), w("");
  }, Dt = (S) => {
    h((P) => ({
      ...P,
      selectedTools: ys(P.selectedTools, S)
    })), w("");
  }, Bl = (S) => {
    N(S);
  }, Rc = () => {
    if (!_) return null;
    const S = _.product, P = tn(S == null ? void 0 : S.price), O = _.imageUrl ?? (S == null ? void 0 : S.imageUrl), H = (m.selectedTools ?? []).includes(_.key);
    return /* @__PURE__ */ d.jsx(
      "div",
      {
        className: "tfdWizard__modalBackdrop",
        role: "presentation",
        onClick: () => N(null),
        children: /* @__PURE__ */ d.jsxs(
          "div",
          {
            className: "tfdWizard__productModal",
            role: "dialog",
            "aria-modal": "true",
            "aria-label": (S == null ? void 0 : S.title) ?? _.name,
            onClick: (We) => We.stopPropagation(),
            children: [
              /* @__PURE__ */ d.jsx(
                "button",
                {
                  type: "button",
                  className: "tfdWizard__modalClose",
                  onClick: () => N(null),
                  "aria-label": "Produktansicht schließen",
                  children: "×"
                }
              ),
              /* @__PURE__ */ d.jsx("div", { className: "tfdWizard__productModalMedia", children: O ? /* @__PURE__ */ d.jsx("img", { src: O, alt: (S == null ? void 0 : S.title) ?? _.name }) : /* @__PURE__ */ d.jsx("div", { className: "tfdWizard__productModalPlaceholder", children: "No1" }) }),
              /* @__PURE__ */ d.jsxs("div", { className: "tfdWizard__productModalBody", children: [
                /* @__PURE__ */ d.jsx("span", { children: "Produktdetails" }),
                /* @__PURE__ */ d.jsx("h3", { children: (S == null ? void 0 : S.title) ?? _.name }),
                P && /* @__PURE__ */ d.jsx("strong", { children: P }),
                _.description && /* @__PURE__ */ d.jsx("p", { children: _.description }),
                /* @__PURE__ */ d.jsxs("div", { className: "tfdWizard__productFacts", children: [
                  (S == null ? void 0 : S.sku) && /* @__PURE__ */ d.jsxs("div", { children: [
                    /* @__PURE__ */ d.jsx("em", { children: "SKU" }),
                    /* @__PURE__ */ d.jsx("b", { children: S.sku })
                  ] }),
                  _.level && /* @__PURE__ */ d.jsxs("div", { children: [
                    /* @__PURE__ */ d.jsx("em", { children: "Tool-Level" }),
                    /* @__PURE__ */ d.jsx("b", { children: _.level })
                  ] }),
                  /* @__PURE__ */ d.jsxs("div", { children: [
                    /* @__PURE__ */ d.jsx("em", { children: "Empfehlung" }),
                    /* @__PURE__ */ d.jsx("b", { children: _.fromCombination ? "Exakt passend" : "Passend" })
                  ] })
                ] }),
                /* @__PURE__ */ d.jsx(
                  "button",
                  {
                    type: "button",
                    className: "tfdWizard__modalSelect",
                    onClick: () => Dt(_.key),
                    "aria-pressed": H,
                    children: H ? "Aus Auswahl entfernen" : "Zur Auswahl hinzufügen"
                  }
                )
              ] })
            ]
          }
        )
      }
    );
  }, Oc = async () => {
    if (!mt || g) return;
    k(!0), w("");
    const S = ks(e, m, M, ie);
    try {
      await kp(t, S), w("Anfrage wurde gespeichert. Wir melden uns mit der passenden Empfehlung.");
    } catch (P) {
      const O = P instanceof Error ? P.message : "Unbekannter API-Fehler";
      w(`Anfrage konnte nicht gesendet werden: ${O}`);
    } finally {
      k(!1);
    }
  }, Dc = () => {
    var bt, An;
    const S = (m.vehicles ?? []).map((L) => {
      var de;
      return ((de = W.find((A) => A.value === L)) == null ? void 0 : de.title) ?? L;
    }).slice(0, 4), P = (m.services ?? []).map((L) => {
      var de;
      return ((de = Fn.find((A) => A.value === L)) == null ? void 0 : de.title) ?? L;
    }).slice(0, 4), O = (bt = zr.find((L) => L.value === m.tool)) == null ? void 0 : bt.title, H = (ie.recommendedTools ?? []).filter((L) => (m.selectedTools ?? []).includes(L.key)), We = H.reduce((L, de) => {
      var Ke;
      const A = Number((Ke = de.product) == null ? void 0 : Ke.price);
      return Number.isFinite(A) ? L + A : L;
    }, 0), ht = !!((An = m.vehicles) != null && An.length && m.tool), ge = !!(m.tool && m.tool !== "none");
    return /* @__PURE__ */ d.jsxs("aside", { className: "tfdWizard__promoAside", "aria-label": "Tool Empfehlungen", children: [
      (S.length > 0 || P.length > 0 || O) && /* @__PURE__ */ d.jsxs("div", { className: "tfdWizard__promoCard tfdWizard__summaryCard", children: [
        /* @__PURE__ */ d.jsx("span", { children: "Deine Auswahl" }),
        O && /* @__PURE__ */ d.jsxs("p", { children: [
          /* @__PURE__ */ d.jsx("b", { children: "Tool:" }),
          " ",
          O
        ] }),
        S.length > 0 && /* @__PURE__ */ d.jsxs("p", { children: [
          /* @__PURE__ */ d.jsx("b", { children: "Fahrzeuge:" }),
          " ",
          S.join(", ")
        ] }),
        P.length > 0 && /* @__PURE__ */ d.jsxs("p", { children: [
          /* @__PURE__ */ d.jsx("b", { children: "Leistungen:" }),
          " ",
          P.join(", ")
        ] })
      ] }),
      !ge && /* @__PURE__ */ d.jsxs("div", { className: "tfdWizard__promoCard tfdWizard__promoCard--accent", children: [
        /* @__PURE__ */ d.jsx("span", { children: "Tool Werbung" }),
        /* @__PURE__ */ d.jsx("strong", { children: "Passendes Leseschreibtool" }),
        /* @__PURE__ */ d.jsx("p", { children: "Für den Händlerstart brauchst du ein unterstütztes Tool. Wir prüfen Protokolle und Fahrzeugbereiche." }),
        ht && a && /* @__PURE__ */ d.jsx("div", { className: "tfdWizard__miniNotice", children: a }),
        m.tool === "none" && /* @__PURE__ */ d.jsx("div", { className: "tfdWizard__miniNotice", children: "Ohne eigenes Tuning-Tool ist noch keine Händler-Aktivierung möglich." }),
        ht ? v ? /* @__PURE__ */ d.jsx("div", { className: "tfdWizard__miniNotice", children: "Empfehlung wird aus dem Backend geladen..." }) : ie.recommendedTools && ie.recommendedTools.length > 0 ? /* @__PURE__ */ d.jsx("div", { className: "tfdWizard__adList", children: ie.recommendedTools.slice(0, 4).map((L) => {
          var A;
          const de = tn((A = L.product) == null ? void 0 : A.price);
          return /* @__PURE__ */ d.jsxs("article", { className: L.imageUrl ? "tfdWizard__adItem--withImage" : void 0, children: [
            L.imageUrl && /* @__PURE__ */ d.jsx("img", { className: "tfdWizard__adImage", src: L.imageUrl, alt: L.name, loading: "lazy" }),
            /* @__PURE__ */ d.jsxs("div", { className: "tfdWizard__adBody", children: [
              /* @__PURE__ */ d.jsx("b", { children: L.name }),
              L.fromCombination && /* @__PURE__ */ d.jsx("span", { className: "tfdWizard__adBadge", children: "Passt zu deiner Auswahl" }),
              L.description && /* @__PURE__ */ d.jsx("small", { children: L.description }),
              L.product && /* @__PURE__ */ d.jsxs("div", { className: "tfdWizard__adProduct", children: [
                de && /* @__PURE__ */ d.jsx("span", { className: "tfdWizard__adPrice", children: de }),
                /* @__PURE__ */ d.jsx(
                  "button",
                  {
                    type: "button",
                    className: "tfdWizard__adLink",
                    onClick: () => Bl(L),
                    children: "Produkt ansehen"
                  }
                )
              ] })
            ] })
          ] }, L.key);
        }) }) : ht ? /* @__PURE__ */ d.jsx("div", { className: "tfdWizard__miniNotice", children: "Für diese Fahrzeugauswahl ist noch keine passende Tool-Empfehlung hinterlegt." }) : /* @__PURE__ */ d.jsxs("div", { className: "tfdWizard__tagList", children: [
          /* @__PURE__ */ d.jsx("em", { children: "Autotuner" }),
          /* @__PURE__ */ d.jsx("em", { children: "KESS3" }),
          /* @__PURE__ */ d.jsx("em", { children: "FLEX" })
        ] }) : /* @__PURE__ */ d.jsx("div", { className: "tfdWizard__miniNotice", children: "Beantworte Fahrzeuge und Tool-Status, um passende Tools zu sehen." })
      ] }),
      l.toolDisclaimer && te.key === "vehicleTool" && /* @__PURE__ */ d.jsx("div", { className: "tfdWizard__miniNotice", children: l.toolDisclaimer }),
      te.key === "contact" && /* @__PURE__ */ d.jsxs("div", { className: "tfdWizard__promoCard tfdWizard__selectedProductsCard", children: [
        /* @__PURE__ */ d.jsx("span", { children: "Ausgewählte Produkte" }),
        H.length > 0 ? /* @__PURE__ */ d.jsxs(d.Fragment, { children: [
          /* @__PURE__ */ d.jsxs("strong", { children: [
            H.length,
            " Position",
            H.length === 1 ? "" : "en"
          ] }),
          /* @__PURE__ */ d.jsx("div", { className: "tfdWizard__selectedProductsList", children: H.map((L) => {
            var A, Ke, nt, gt, ou;
            const de = tn((A = L.product) == null ? void 0 : A.price);
            return /* @__PURE__ */ d.jsxs("article", { children: [
              (L.imageUrl || ((Ke = L.product) == null ? void 0 : Ke.imageUrl)) && /* @__PURE__ */ d.jsx("img", { src: L.imageUrl ?? ((nt = L.product) == null ? void 0 : nt.imageUrl), alt: ((gt = L.product) == null ? void 0 : gt.title) ?? L.name, loading: "lazy" }),
              /* @__PURE__ */ d.jsxs("div", { children: [
                /* @__PURE__ */ d.jsx("b", { children: ((ou = L.product) == null ? void 0 : ou.title) ?? L.name }),
                de && /* @__PURE__ */ d.jsx("em", { children: de }),
                /* @__PURE__ */ d.jsx("button", { type: "button", onClick: () => Bl(L), children: "Details" })
              ] })
            ] }, L.key);
          }) }),
          We > 0 && /* @__PURE__ */ d.jsxs("p", { className: "tfdWizard__selectedProductsTotal", children: [
            /* @__PURE__ */ d.jsx("b", { children: "Summe:" }),
            " ",
            tn(String(We))
          ] })
        ] }) : /* @__PURE__ */ d.jsx("p", { children: "Keine Produkte ausgewählt. Du kannst im vorherigen Schritt passende Tools auswählen." })
      ] })
    ] });
  }, Wc = () => {
    var S, P, O, H, We, ht;
    switch (te.key) {
      case "entry":
        return /* @__PURE__ */ d.jsxs(d.Fragment, { children: [
          /* @__PURE__ */ d.jsxs("section", { className: "tfdWizard__block", children: [
            /* @__PURE__ */ d.jsx("h3", { className: "tfdWizard__title", children: "Was ist dein Ziel?" }),
            V(Ep, "goal")
          ] }),
          /* @__PURE__ */ d.jsxs("section", { className: "tfdWizard__block", children: [
            /* @__PURE__ */ d.jsx("h3", { className: "tfdWizard__title", children: "Wie viel Erfahrung hast du?" }),
            V(zp, "experience")
          ] })
        ] });
      case "vehicleTool":
        return /* @__PURE__ */ d.jsxs(d.Fragment, { children: [
          /* @__PURE__ */ d.jsxs("section", { className: "tfdWizard__block", children: [
            /* @__PURE__ */ d.jsx("h3", { className: "tfdWizard__title", children: "Welche Fahrzeuge möchtest du bearbeiten?" }),
            /* @__PURE__ */ d.jsx(
              "button",
              {
                type: "button",
                className: "tfdWizard__selectAllButton",
                onClick: Y,
                "data-testid": "vehicles-select-all-button",
                children: F ? "Alle abwählen" : "Alle auswählen"
              }
            ),
            V(W, "vehicles", !0)
          ] }),
          /* @__PURE__ */ d.jsxs("section", { className: "tfdWizard__block", children: [
            /* @__PURE__ */ d.jsx("h3", { className: "tfdWizard__title", children: "Hast du bereits ein eigenes Tool?" }),
            /* @__PURE__ */ d.jsx("p", { className: "tfdWizard__lead", children: "Ohne eigenes Tuning-Tool ist keine direkte Händler-Aktivierung möglich. Dann geht es zuerst in die Tool-Beratung." }),
            V(zr, "tool")
          ] })
        ] });
      case "offerVolume":
        return /* @__PURE__ */ d.jsxs(d.Fragment, { children: [
          /* @__PURE__ */ d.jsxs("section", { className: "tfdWizard__block", children: [
            /* @__PURE__ */ d.jsx("h3", { className: "tfdWizard__title", children: "Welche Leistungen willst du anbieten?" }),
            V(Fn, "services", !0)
          ] }),
          /* @__PURE__ */ d.jsxs("section", { className: "tfdWizard__block", children: [
            /* @__PURE__ */ d.jsx("h3", { className: "tfdWizard__title", children: "Wie viele Fahrzeuge erwartest du?" }),
            V(jp, "volume")
          ] })
        ] });
      case "recommendation": {
        const ge = Array.from(new Set([...ie.bullets ?? [], ...Op].filter(Boolean))), bt = ((S = ie.recommendedTools) == null ? void 0 : S.slice(0, 4)) ?? [], An = m.selectedTools ?? [], L = bt.filter((A) => An.includes(A.key)), de = L.reduce((A, Ke) => {
          var gt;
          const nt = Number((gt = Ke.product) == null ? void 0 : gt.price);
          return Number.isFinite(nt) ? A + nt : A;
        }, 0);
        return /* @__PURE__ */ d.jsxs("section", { className: "tfdWizard__block tfdWizard__recommendationStep", children: [
          /* @__PURE__ */ d.jsxs("div", { className: "tfdWizard__recommendationHero", children: [
            /* @__PURE__ */ d.jsx("span", { children: "Deine Empfehlung" }),
            /* @__PURE__ */ d.jsx("h3", { children: ie.packageName }),
            /* @__PURE__ */ d.jsx("p", { children: ie.summary })
          ] }),
          /* @__PURE__ */ d.jsxs("div", { className: "tfdWizard__recommendationPanel", children: [
            /* @__PURE__ */ d.jsxs("div", { className: "tfdWizard__recommendationPanelHeader", children: [
              /* @__PURE__ */ d.jsx("span", { children: "Passende Tools" }),
              v && /* @__PURE__ */ d.jsx("em", { children: "Wird geladen..." })
            ] }),
            bt.length > 0 ? /* @__PURE__ */ d.jsx("div", { className: "tfdWizard__toolShowcase", children: bt.map((A) => {
              var gt;
              const Ke = tn((gt = A.product) == null ? void 0 : gt.price), nt = An.includes(A.key);
              return /* @__PURE__ */ d.jsxs(
                "article",
                {
                  className: [
                    "tfdWizard__toolShowcaseItem",
                    A.imageUrl ? "tfdWizard__toolShowcaseItem--withImage" : "",
                    nt ? "tfdWizard__toolShowcaseItem--selected" : ""
                  ].filter(Boolean).join(" "),
                  children: [
                    A.imageUrl && /* @__PURE__ */ d.jsx("img", { className: "tfdWizard__toolShowcaseImage", src: A.imageUrl, alt: A.name, loading: "lazy" }),
                    /* @__PURE__ */ d.jsxs("div", { children: [
                      /* @__PURE__ */ d.jsxs("div", { className: "tfdWizard__toolShowcaseTop", children: [
                        /* @__PURE__ */ d.jsx("b", { children: A.name }),
                        /* @__PURE__ */ d.jsx(
                          "button",
                          {
                            type: "button",
                            className: "tfdWizard__cartToggle",
                            onClick: () => Dt(A.key),
                            "aria-pressed": nt,
                            children: nt ? "Ausgewählt" : "Auswählen"
                          }
                        )
                      ] }),
                      A.fromCombination && /* @__PURE__ */ d.jsx("small", { children: "Passt exakt zu deiner Auswahl" }),
                      A.description && /* @__PURE__ */ d.jsx("p", { children: A.description }),
                      /* @__PURE__ */ d.jsxs("div", { className: "tfdWizard__toolShowcaseMeta", children: [
                        A.level && /* @__PURE__ */ d.jsx("span", { children: A.level }),
                        Ke && /* @__PURE__ */ d.jsx("strong", { children: Ke }),
                        A.product && /* @__PURE__ */ d.jsx("button", { type: "button", onClick: () => Bl(A), children: "Produkt ansehen" })
                      ] })
                    ] })
                  ]
                },
                A.key
              );
            }) }) : /* @__PURE__ */ d.jsx("div", { className: "tfdWizard__miniNotice", children: "Für diese Auswahl ist noch keine passende Tool-Empfehlung hinterlegt." }),
            /* @__PURE__ */ d.jsxs("div", { className: "tfdWizard__cartSummary", children: [
              /* @__PURE__ */ d.jsx("span", { children: "Auswahl" }),
              L.length > 0 ? /* @__PURE__ */ d.jsxs(d.Fragment, { children: [
                /* @__PURE__ */ d.jsxs("strong", { children: [
                  L.length,
                  " Position",
                  L.length === 1 ? "" : "en",
                  de > 0 ? ` · ${tn(String(de))}` : ""
                ] }),
                /* @__PURE__ */ d.jsx("p", { children: L.map((A) => A.name).join(", ") })
              ] }) : /* @__PURE__ */ d.jsx("p", { children: "Wähle ein oder mehrere passende Tools aus. Die Auswahl wird mit deiner Anfrage übergeben." })
            ] })
          ] }),
          /* @__PURE__ */ d.jsxs("div", { className: "tfdWizard__extrasPanel", children: [
            /* @__PURE__ */ d.jsx("span", { children: "Mit drin" }),
            /* @__PURE__ */ d.jsx("ul", { children: ge.map((A) => /* @__PURE__ */ d.jsx("li", { children: A }, A)) })
          ] })
        ] });
      }
      case "contact":
        return /* @__PURE__ */ d.jsxs("section", { className: "tfdWizard__block", children: [
          /* @__PURE__ */ d.jsx("h3", { className: "tfdWizard__title", children: "Kontakt aufnehmen" }),
          /* @__PURE__ */ d.jsx("p", { className: "tfdWizard__lead", children: "Sende uns deine Angaben. Wir prüfen dein Partner-Modell und melden uns mit der passenden Empfehlung." }),
          /* @__PURE__ */ d.jsxs("div", { className: "tfdWizard__form", children: [
            /* @__PURE__ */ d.jsxs("label", { children: [
              /* @__PURE__ */ d.jsx("span", { children: "Name *" }),
              /* @__PURE__ */ d.jsx(
                "input",
                {
                  value: ((P = m.contact) == null ? void 0 : P.name) ?? "",
                  onChange: (ge) => et("name", ge.target.value),
                  "data-testid": "contact-name-input"
                }
              )
            ] }),
            /* @__PURE__ */ d.jsxs("label", { children: [
              /* @__PURE__ */ d.jsx("span", { children: "Firma" }),
              /* @__PURE__ */ d.jsx(
                "input",
                {
                  value: ((O = m.contact) == null ? void 0 : O.company) ?? "",
                  onChange: (ge) => et("company", ge.target.value),
                  "data-testid": "contact-company-input"
                }
              )
            ] }),
            /* @__PURE__ */ d.jsxs("label", { children: [
              /* @__PURE__ */ d.jsx("span", { children: "E-Mail *" }),
              /* @__PURE__ */ d.jsx(
                "input",
                {
                  type: "email",
                  value: ((H = m.contact) == null ? void 0 : H.email) ?? "",
                  onChange: (ge) => et("email", ge.target.value),
                  "data-testid": "contact-email-input"
                }
              )
            ] }),
            /* @__PURE__ */ d.jsxs("label", { children: [
              /* @__PURE__ */ d.jsx("span", { children: "Telefon" }),
              /* @__PURE__ */ d.jsx(
                "input",
                {
                  value: ((We = m.contact) == null ? void 0 : We.phone) ?? "",
                  onChange: (ge) => et("phone", ge.target.value),
                  "data-testid": "contact-phone-input"
                }
              )
            ] }),
            /* @__PURE__ */ d.jsxs("label", { className: "tfdWizard__fieldFull", children: [
              /* @__PURE__ */ d.jsx("span", { children: "Nachricht" }),
              /* @__PURE__ */ d.jsx(
                "textarea",
                {
                  value: ((ht = m.contact) == null ? void 0 : ht.message) ?? "",
                  onChange: (ge) => et("message", ge.target.value),
                  "data-testid": "contact-message-input"
                }
              )
            ] })
          ] }),
          /* @__PURE__ */ d.jsxs("label", { className: "tfdWizard__privacy", children: [
            /* @__PURE__ */ d.jsx(
              "input",
              {
                type: "checkbox",
                checked: m.privacyAccepted === !0,
                onChange: (ge) => tt(ge.target.checked),
                "data-testid": "privacy-accepted-checkbox"
              }
            ),
            /* @__PURE__ */ d.jsx("span", { children: l.privacyNotice })
          ] }),
          x && /* @__PURE__ */ d.jsx("div", { className: "tfdWizard__notice", children: x })
        ] });
      default:
        return null;
    }
  };
  return /* @__PURE__ */ d.jsxs(
    "div",
    {
      className: `tfdWizard tfdWizard--${r}`,
      style: { "--partner-wizard-primary": n },
      "data-testid": "partner-wizard",
      children: [
        /* @__PURE__ */ d.jsxs("header", { className: "tfdWizard__header", children: [
          /* @__PURE__ */ d.jsx("span", { children: "Händler werden" }),
          /* @__PURE__ */ d.jsx("h2", { children: l.headline }),
          /* @__PURE__ */ d.jsx("p", { children: l.intro })
        ] }),
        o && /* @__PURE__ */ d.jsx("div", { className: "tfdWizard__error", children: o }),
        /* @__PURE__ */ d.jsx("div", { className: "tfdWizard__progress", children: /* @__PURE__ */ d.jsx("div", { style: { width: `${Mn}%` } }) }),
        /* @__PURE__ */ d.jsxs("div", { className: "tfdWizard__layout", children: [
          /* @__PURE__ */ d.jsx("aside", { className: "tfdWizard__steps", "aria-label": "Wizard Schritte", children: Vr.map((S, P) => {
            const O = [
              P === s ? "active" : "",
              P < s ? "done" : ""
            ].filter(Boolean).join(" ");
            return /* @__PURE__ */ d.jsxs(
              "button",
              {
                type: "button",
                className: O,
                onClick: () => {
                  P <= s && (c(P), w(""));
                },
                "data-testid": `step-${S.key}`,
                children: [
                  /* @__PURE__ */ d.jsx("b", { children: P < s ? /* @__PURE__ */ d.jsx("svg", { viewBox: "0 0 24 24", "aria-hidden": "true", focusable: "false", children: /* @__PURE__ */ d.jsx("path", { d: "M5 12.5l4.2 4.2L19 7" }) }) : P + 1 }),
                  /* @__PURE__ */ d.jsx("span", { children: S.label })
                ]
              },
              S.key
            );
          }) }),
          /* @__PURE__ */ d.jsxs("div", { className: `tfdWizard__body ${te.key === "recommendation" ? "tfdWizard__body--full" : ""}`, children: [
            /* @__PURE__ */ d.jsx("main", { className: "tfdWizard__mainPane", children: Wc() }),
            te.key !== "recommendation" && Dc()
          ] })
        ] }),
        /* @__PURE__ */ d.jsxs("div", { className: "tfdWizard__actions", children: [
          /* @__PURE__ */ d.jsx(
            "button",
            {
              type: "button",
              onClick: be,
              disabled: s === 0,
              "data-testid": "wizard-back-button",
              children: "Zurück"
            }
          ),
          te.key === "contact" ? /* @__PURE__ */ d.jsx(
            "button",
            {
              type: "button",
              onClick: Oc,
              disabled: !mt || g,
              "data-testid": "wizard-submit-button",
              children: g ? "Senden..." : ie.cta
            }
          ) : /* @__PURE__ */ d.jsx(
            "button",
            {
              type: "button",
              onClick: qt,
              disabled: !mt,
              "data-testid": "wizard-next-button",
              children: "Weiter"
            }
          )
        ] }),
        Rc()
      ]
    }
  );
}
class Bp extends HTMLElement {
  constructor() {
    super(...arguments);
    Vl(this, "root");
    Vl(this, "mount");
  }
  connectedCallback() {
    if (this.root)
      return;
    this.mount = document.createElement("div"), this.appendChild(this.mount);
    const n = this.getAttribute("client-id") || "no1", r = this.getAttribute("api-base-url") || "", l = this.getAttribute("primary-color") || "#E31E24", o = (this.getAttribute("theme") || "light") === "dark" ? "dark" : "light";
    this.style.setProperty("--partner-wizard-primary", l), this.root = Fc(this.mount), this.root.render(
      /* @__PURE__ */ d.jsx(
        $p,
        {
          clientId: n,
          apiBaseUrl: r,
          primaryColor: l,
          theme: o
        }
      )
    );
  }
  disconnectedCallback() {
    var n;
    (n = this.root) == null || n.unmount(), this.root = void 0, this.mount = void 0;
  }
}
customElements.get("tfd-partner-wizard") || customElements.define("tfd-partner-wizard", Bp);
export {
  $p as PartnerWizard
};
