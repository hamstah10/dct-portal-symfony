var rd = Object.defineProperty;
var ld = (e, t, n) => t in e ? rd(e, t, { enumerable: !0, configurable: !0, writable: !0, value: n }) : e[t] = n;
var ei = (e, t, n) => ld(e, typeof t != "symbol" ? t + "" : t, n);
var Ws = { exports: {} }, Dl = {}, Fs = { exports: {} }, I = {};
/**
 * @license React
 * react.production.min.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */
var Cr = Symbol.for("react.element"), id = Symbol.for("react.portal"), od = Symbol.for("react.fragment"), ud = Symbol.for("react.strict_mode"), sd = Symbol.for("react.profiler"), ad = Symbol.for("react.provider"), cd = Symbol.for("react.context"), dd = Symbol.for("react.forward_ref"), fd = Symbol.for("react.suspense"), pd = Symbol.for("react.memo"), md = Symbol.for("react.lazy"), Su = Symbol.iterator;
function hd(e) {
  return e === null || typeof e != "object" ? null : (e = Su && e[Su] || e["@@iterator"], typeof e == "function" ? e : null);
}
var Ms = { isMounted: function() {
  return !1;
}, enqueueForceUpdate: function() {
}, enqueueReplaceState: function() {
}, enqueueSetState: function() {
} }, Os = Object.assign, As = {};
function Fn(e, t, n) {
  this.props = e, this.context = t, this.refs = As, this.updater = n || Ms;
}
Fn.prototype.isReactComponent = {};
Fn.prototype.setState = function(e, t) {
  if (typeof e != "object" && typeof e != "function" && e != null) throw Error("setState(...): takes an object of state variables to update or a function which returns an object of state variables.");
  this.updater.enqueueSetState(this, e, t, "setState");
};
Fn.prototype.forceUpdate = function(e) {
  this.updater.enqueueForceUpdate(this, e, "forceUpdate");
};
function Rs() {
}
Rs.prototype = Fn.prototype;
function xo(e, t, n) {
  this.props = e, this.context = t, this.refs = As, this.updater = n || Ms;
}
var zo = xo.prototype = new Rs();
zo.constructor = xo;
Os(zo, Fn.prototype);
zo.isPureReactComponent = !0;
var _u = Array.isArray, Ds = Object.prototype.hasOwnProperty, Eo = { current: null }, Is = { key: !0, ref: !0, __self: !0, __source: !0 };
function Us(e, t, n) {
  var r, l = {}, i = null, o = null;
  if (t != null) for (r in t.ref !== void 0 && (o = t.ref), t.key !== void 0 && (i = "" + t.key), t) Ds.call(t, r) && !Is.hasOwnProperty(r) && (l[r] = t[r]);
  var u = arguments.length - 2;
  if (u === 1) l.children = n;
  else if (1 < u) {
    for (var s = Array(u), d = 0; d < u; d++) s[d] = arguments[d + 2];
    l.children = s;
  }
  if (e && e.defaultProps) for (r in u = e.defaultProps, u) l[r] === void 0 && (l[r] = u[r]);
  return { $$typeof: Cr, type: e, key: i, ref: o, props: l, _owner: Eo.current };
}
function gd(e, t) {
  return { $$typeof: Cr, type: e.type, key: t, ref: e.ref, props: e.props, _owner: e._owner };
}
function No(e) {
  return typeof e == "object" && e !== null && e.$$typeof === Cr;
}
function vd(e) {
  var t = { "=": "=0", ":": "=2" };
  return "$" + e.replace(/[=:]/g, function(n) {
    return t[n];
  });
}
var xu = /\/+/g;
function ti(e, t) {
  return typeof e == "object" && e !== null && e.key != null ? vd("" + e.key) : t.toString(36);
}
function tl(e, t, n, r, l) {
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
        case Cr:
        case id:
          o = !0;
      }
  }
  if (o) return o = e, l = l(o), e = r === "" ? "." + ti(o, 0) : r, _u(l) ? (n = "", e != null && (n = e.replace(xu, "$&/") + "/"), tl(l, t, n, "", function(d) {
    return d;
  })) : l != null && (No(l) && (l = gd(l, n + (!l.key || o && o.key === l.key ? "" : ("" + l.key).replace(xu, "$&/") + "/") + e)), t.push(l)), 1;
  if (o = 0, r = r === "" ? "." : r + ":", _u(e)) for (var u = 0; u < e.length; u++) {
    i = e[u];
    var s = r + ti(i, u);
    o += tl(i, t, n, s, l);
  }
  else if (s = hd(e), typeof s == "function") for (e = s.call(e), u = 0; !(i = e.next()).done; ) i = i.value, s = r + ti(i, u++), o += tl(i, t, n, s, l);
  else if (i === "object") throw t = String(e), Error("Objects are not valid as a React child (found: " + (t === "[object Object]" ? "object with keys {" + Object.keys(e).join(", ") + "}" : t) + "). If you meant to render a collection of children, use an array instead.");
  return o;
}
function Ar(e, t, n) {
  if (e == null) return e;
  var r = [], l = 0;
  return tl(e, r, "", "", function(i) {
    return t.call(n, i, l++);
  }), r;
}
function yd(e) {
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
var ke = { current: null }, nl = { transition: null }, wd = { ReactCurrentDispatcher: ke, ReactCurrentBatchConfig: nl, ReactCurrentOwner: Eo };
function $s() {
  throw Error("act(...) is not supported in production builds of React.");
}
I.Children = { map: Ar, forEach: function(e, t, n) {
  Ar(e, function() {
    t.apply(this, arguments);
  }, n);
}, count: function(e) {
  var t = 0;
  return Ar(e, function() {
    t++;
  }), t;
}, toArray: function(e) {
  return Ar(e, function(t) {
    return t;
  }) || [];
}, only: function(e) {
  if (!No(e)) throw Error("React.Children.only expected to receive a single React element child.");
  return e;
} };
I.Component = Fn;
I.Fragment = od;
I.Profiler = sd;
I.PureComponent = xo;
I.StrictMode = ud;
I.Suspense = fd;
I.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED = wd;
I.act = $s;
I.cloneElement = function(e, t, n) {
  if (e == null) throw Error("React.cloneElement(...): The argument must be a React element, but you passed " + e + ".");
  var r = Os({}, e.props), l = e.key, i = e.ref, o = e._owner;
  if (t != null) {
    if (t.ref !== void 0 && (i = t.ref, o = Eo.current), t.key !== void 0 && (l = "" + t.key), e.type && e.type.defaultProps) var u = e.type.defaultProps;
    for (s in t) Ds.call(t, s) && !Is.hasOwnProperty(s) && (r[s] = t[s] === void 0 && u !== void 0 ? u[s] : t[s]);
  }
  var s = arguments.length - 2;
  if (s === 1) r.children = n;
  else if (1 < s) {
    u = Array(s);
    for (var d = 0; d < s; d++) u[d] = arguments[d + 2];
    r.children = u;
  }
  return { $$typeof: Cr, type: e.type, key: l, ref: i, props: r, _owner: o };
};
I.createContext = function(e) {
  return e = { $$typeof: cd, _currentValue: e, _currentValue2: e, _threadCount: 0, Provider: null, Consumer: null, _defaultValue: null, _globalName: null }, e.Provider = { $$typeof: ad, _context: e }, e.Consumer = e;
};
I.createElement = Us;
I.createFactory = function(e) {
  var t = Us.bind(null, e);
  return t.type = e, t;
};
I.createRef = function() {
  return { current: null };
};
I.forwardRef = function(e) {
  return { $$typeof: dd, render: e };
};
I.isValidElement = No;
I.lazy = function(e) {
  return { $$typeof: md, _payload: { _status: -1, _result: e }, _init: yd };
};
I.memo = function(e, t) {
  return { $$typeof: pd, type: e, compare: t === void 0 ? null : t };
};
I.startTransition = function(e) {
  var t = nl.transition;
  nl.transition = {};
  try {
    e();
  } finally {
    nl.transition = t;
  }
};
I.unstable_act = $s;
I.useCallback = function(e, t) {
  return ke.current.useCallback(e, t);
};
I.useContext = function(e) {
  return ke.current.useContext(e);
};
I.useDebugValue = function() {
};
I.useDeferredValue = function(e) {
  return ke.current.useDeferredValue(e);
};
I.useEffect = function(e, t) {
  return ke.current.useEffect(e, t);
};
I.useId = function() {
  return ke.current.useId();
};
I.useImperativeHandle = function(e, t, n) {
  return ke.current.useImperativeHandle(e, t, n);
};
I.useInsertionEffect = function(e, t) {
  return ke.current.useInsertionEffect(e, t);
};
I.useLayoutEffect = function(e, t) {
  return ke.current.useLayoutEffect(e, t);
};
I.useMemo = function(e, t) {
  return ke.current.useMemo(e, t);
};
I.useReducer = function(e, t, n) {
  return ke.current.useReducer(e, t, n);
};
I.useRef = function(e) {
  return ke.current.useRef(e);
};
I.useState = function(e) {
  return ke.current.useState(e);
};
I.useSyncExternalStore = function(e, t, n) {
  return ke.current.useSyncExternalStore(e, t, n);
};
I.useTransition = function() {
  return ke.current.useTransition();
};
I.version = "18.3.1";
Fs.exports = I;
var ee = Fs.exports;
/**
 * @license React
 * react-jsx-runtime.production.min.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */
var kd = ee, Sd = Symbol.for("react.element"), _d = Symbol.for("react.fragment"), xd = Object.prototype.hasOwnProperty, zd = kd.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED.ReactCurrentOwner, Ed = { key: !0, ref: !0, __self: !0, __source: !0 };
function Vs(e, t, n) {
  var r, l = {}, i = null, o = null;
  n !== void 0 && (i = "" + n), t.key !== void 0 && (i = "" + t.key), t.ref !== void 0 && (o = t.ref);
  for (r in t) xd.call(t, r) && !Ed.hasOwnProperty(r) && (l[r] = t[r]);
  if (e && e.defaultProps) for (r in t = e.defaultProps, t) l[r] === void 0 && (l[r] = t[r]);
  return { $$typeof: Sd, type: e, key: i, ref: o, props: l, _owner: zd.current };
}
Dl.Fragment = _d;
Dl.jsx = Vs;
Dl.jsxs = Vs;
Ws.exports = Dl;
var a = Ws.exports, Ks = { exports: {} }, Me = {}, Bs = { exports: {} }, Hs = {};
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
  function t(k, M) {
    var R = k.length;
    k.push(M);
    e: for (; 0 < R; ) {
      var H = R - 1 >>> 1, re = k[H];
      if (0 < l(re, M)) k[H] = M, k[R] = re, R = H;
      else break e;
    }
  }
  function n(k) {
    return k.length === 0 ? null : k[0];
  }
  function r(k) {
    if (k.length === 0) return null;
    var M = k[0], R = k.pop();
    if (R !== M) {
      k[0] = R;
      e: for (var H = 0, re = k.length, ln = re >>> 1; H < ln; ) {
        var be = 2 * (H + 1) - 1, An = k[be], ot = be + 1, on = k[ot];
        if (0 > l(An, R)) ot < re && 0 > l(on, An) ? (k[H] = on, k[ot] = R, H = ot) : (k[H] = An, k[be] = R, H = be);
        else if (ot < re && 0 > l(on, R)) k[H] = on, k[ot] = R, H = ot;
        else break e;
      }
    }
    return M;
  }
  function l(k, M) {
    var R = k.sortIndex - M.sortIndex;
    return R !== 0 ? R : k.id - M.id;
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
  var s = [], d = [], m = 1, g = null, h = 3, S = !1, z = !1, _ = !1, B = typeof setTimeout == "function" ? setTimeout : null, f = typeof clearTimeout == "function" ? clearTimeout : null, c = typeof setImmediate < "u" ? setImmediate : null;
  typeof navigator < "u" && navigator.scheduling !== void 0 && navigator.scheduling.isInputPending !== void 0 && navigator.scheduling.isInputPending.bind(navigator.scheduling);
  function p(k) {
    for (var M = n(d); M !== null; ) {
      if (M.callback === null) r(d);
      else if (M.startTime <= k) r(d), M.sortIndex = M.expirationTime, t(s, M);
      else break;
      M = n(d);
    }
  }
  function y(k) {
    if (_ = !1, p(k), !z) if (n(s) !== null) z = !0, Vt(C);
    else {
      var M = n(d);
      M !== null && se(y, M.startTime - k);
    }
  }
  function C(k, M) {
    z = !1, _ && (_ = !1, f(T), T = -1), S = !0;
    var R = h;
    try {
      for (p(M), g = n(s); g !== null && (!(g.expirationTime > M) || k && !ue()); ) {
        var H = g.callback;
        if (typeof H == "function") {
          g.callback = null, h = g.priorityLevel;
          var re = H(g.expirationTime <= M);
          M = e.unstable_now(), typeof re == "function" ? g.callback = re : g === n(s) && r(s), p(M);
        } else r(s);
        g = n(s);
      }
      if (g !== null) var ln = !0;
      else {
        var be = n(d);
        be !== null && se(y, be.startTime - M), ln = !1;
      }
      return ln;
    } finally {
      g = null, h = R, S = !1;
    }
  }
  var N = !1, P = null, T = -1, K = 5, O = -1;
  function ue() {
    return !(e.unstable_now() - O < K);
  }
  function Ut() {
    if (P !== null) {
      var k = e.unstable_now();
      O = k;
      var M = !0;
      try {
        M = P(!0, k);
      } finally {
        M ? $t() : (N = !1, P = null);
      }
    } else N = !1;
  }
  var $t;
  if (typeof c == "function") $t = function() {
    c(Ut);
  };
  else if (typeof MessageChannel < "u") {
    var Wr = new MessageChannel(), Fr = Wr.port2;
    Wr.port1.onmessage = Ut, $t = function() {
      Fr.postMessage(null);
    };
  } else $t = function() {
    B(Ut, 0);
  };
  function Vt(k) {
    P = k, N || (N = !0, $t());
  }
  function se(k, M) {
    T = B(function() {
      k(e.unstable_now());
    }, M);
  }
  e.unstable_IdlePriority = 5, e.unstable_ImmediatePriority = 1, e.unstable_LowPriority = 4, e.unstable_NormalPriority = 3, e.unstable_Profiling = null, e.unstable_UserBlockingPriority = 2, e.unstable_cancelCallback = function(k) {
    k.callback = null;
  }, e.unstable_continueExecution = function() {
    z || S || (z = !0, Vt(C));
  }, e.unstable_forceFrameRate = function(k) {
    0 > k || 125 < k ? console.error("forceFrameRate takes a positive int between 0 and 125, forcing frame rates higher than 125 fps is not supported") : K = 0 < k ? Math.floor(1e3 / k) : 5;
  }, e.unstable_getCurrentPriorityLevel = function() {
    return h;
  }, e.unstable_getFirstCallbackNode = function() {
    return n(s);
  }, e.unstable_next = function(k) {
    switch (h) {
      case 1:
      case 2:
      case 3:
        var M = 3;
        break;
      default:
        M = h;
    }
    var R = h;
    h = M;
    try {
      return k();
    } finally {
      h = R;
    }
  }, e.unstable_pauseExecution = function() {
  }, e.unstable_requestPaint = function() {
  }, e.unstable_runWithPriority = function(k, M) {
    switch (k) {
      case 1:
      case 2:
      case 3:
      case 4:
      case 5:
        break;
      default:
        k = 3;
    }
    var R = h;
    h = k;
    try {
      return M();
    } finally {
      h = R;
    }
  }, e.unstable_scheduleCallback = function(k, M, R) {
    var H = e.unstable_now();
    switch (typeof R == "object" && R !== null ? (R = R.delay, R = typeof R == "number" && 0 < R ? H + R : H) : R = H, k) {
      case 1:
        var re = -1;
        break;
      case 2:
        re = 250;
        break;
      case 5:
        re = 1073741823;
        break;
      case 4:
        re = 1e4;
        break;
      default:
        re = 5e3;
    }
    return re = R + re, k = { id: m++, callback: M, priorityLevel: k, startTime: R, expirationTime: re, sortIndex: -1 }, R > H ? (k.sortIndex = R, t(d, k), n(s) === null && k === n(d) && (_ ? (f(T), T = -1) : _ = !0, se(y, R - H))) : (k.sortIndex = re, t(s, k), z || S || (z = !0, Vt(C))), k;
  }, e.unstable_shouldYield = ue, e.unstable_wrapCallback = function(k) {
    var M = h;
    return function() {
      var R = h;
      h = M;
      try {
        return k.apply(this, arguments);
      } finally {
        h = R;
      }
    };
  };
})(Hs);
Bs.exports = Hs;
var Nd = Bs.exports;
/**
 * @license React
 * react-dom.production.min.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */
var Cd = ee, Fe = Nd;
function w(e) {
  for (var t = "https://reactjs.org/docs/error-decoder.html?invariant=" + e, n = 1; n < arguments.length; n++) t += "&args[]=" + encodeURIComponent(arguments[n]);
  return "Minified React error #" + e + "; visit " + t + " for the full message or use the non-minified dev environment for full errors and additional helpful warnings.";
}
var Qs = /* @__PURE__ */ new Set(), cr = {};
function nn(e, t) {
  Nn(e, t), Nn(e + "Capture", t);
}
function Nn(e, t) {
  for (cr[e] = t, e = 0; e < t.length; e++) Qs.add(t[e]);
}
var ft = !(typeof window > "u" || typeof window.document > "u" || typeof window.document.createElement > "u"), Pi = Object.prototype.hasOwnProperty, jd = /^[:A-Z_a-z\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u02FF\u0370-\u037D\u037F-\u1FFF\u200C-\u200D\u2070-\u218F\u2C00-\u2FEF\u3001-\uD7FF\uF900-\uFDCF\uFDF0-\uFFFD][:A-Z_a-z\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u02FF\u0370-\u037D\u037F-\u1FFF\u200C-\u200D\u2070-\u218F\u2C00-\u2FEF\u3001-\uD7FF\uF900-\uFDCF\uFDF0-\uFFFD\-.0-9\u00B7\u0300-\u036F\u203F-\u2040]*$/, zu = {}, Eu = {};
function Pd(e) {
  return Pi.call(Eu, e) ? !0 : Pi.call(zu, e) ? !1 : jd.test(e) ? Eu[e] = !0 : (zu[e] = !0, !1);
}
function Td(e, t, n, r) {
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
function Ld(e, t, n, r) {
  if (t === null || typeof t > "u" || Td(e, t, n, r)) return !0;
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
function Se(e, t, n, r, l, i, o) {
  this.acceptsBooleans = t === 2 || t === 3 || t === 4, this.attributeName = r, this.attributeNamespace = l, this.mustUseProperty = n, this.propertyName = e, this.type = t, this.sanitizeURL = i, this.removeEmptyString = o;
}
var pe = {};
"children dangerouslySetInnerHTML defaultValue defaultChecked innerHTML suppressContentEditableWarning suppressHydrationWarning style".split(" ").forEach(function(e) {
  pe[e] = new Se(e, 0, !1, e, null, !1, !1);
});
[["acceptCharset", "accept-charset"], ["className", "class"], ["htmlFor", "for"], ["httpEquiv", "http-equiv"]].forEach(function(e) {
  var t = e[0];
  pe[t] = new Se(t, 1, !1, e[1], null, !1, !1);
});
["contentEditable", "draggable", "spellCheck", "value"].forEach(function(e) {
  pe[e] = new Se(e, 2, !1, e.toLowerCase(), null, !1, !1);
});
["autoReverse", "externalResourcesRequired", "focusable", "preserveAlpha"].forEach(function(e) {
  pe[e] = new Se(e, 2, !1, e, null, !1, !1);
});
"allowFullScreen async autoFocus autoPlay controls default defer disabled disablePictureInPicture disableRemotePlayback formNoValidate hidden loop noModule noValidate open playsInline readOnly required reversed scoped seamless itemScope".split(" ").forEach(function(e) {
  pe[e] = new Se(e, 3, !1, e.toLowerCase(), null, !1, !1);
});
["checked", "multiple", "muted", "selected"].forEach(function(e) {
  pe[e] = new Se(e, 3, !0, e, null, !1, !1);
});
["capture", "download"].forEach(function(e) {
  pe[e] = new Se(e, 4, !1, e, null, !1, !1);
});
["cols", "rows", "size", "span"].forEach(function(e) {
  pe[e] = new Se(e, 6, !1, e, null, !1, !1);
});
["rowSpan", "start"].forEach(function(e) {
  pe[e] = new Se(e, 5, !1, e.toLowerCase(), null, !1, !1);
});
var Co = /[\-:]([a-z])/g;
function jo(e) {
  return e[1].toUpperCase();
}
"accent-height alignment-baseline arabic-form baseline-shift cap-height clip-path clip-rule color-interpolation color-interpolation-filters color-profile color-rendering dominant-baseline enable-background fill-opacity fill-rule flood-color flood-opacity font-family font-size font-size-adjust font-stretch font-style font-variant font-weight glyph-name glyph-orientation-horizontal glyph-orientation-vertical horiz-adv-x horiz-origin-x image-rendering letter-spacing lighting-color marker-end marker-mid marker-start overline-position overline-thickness paint-order panose-1 pointer-events rendering-intent shape-rendering stop-color stop-opacity strikethrough-position strikethrough-thickness stroke-dasharray stroke-dashoffset stroke-linecap stroke-linejoin stroke-miterlimit stroke-opacity stroke-width text-anchor text-decoration text-rendering underline-position underline-thickness unicode-bidi unicode-range units-per-em v-alphabetic v-hanging v-ideographic v-mathematical vector-effect vert-adv-y vert-origin-x vert-origin-y word-spacing writing-mode xmlns:xlink x-height".split(" ").forEach(function(e) {
  var t = e.replace(
    Co,
    jo
  );
  pe[t] = new Se(t, 1, !1, e, null, !1, !1);
});
"xlink:actuate xlink:arcrole xlink:role xlink:show xlink:title xlink:type".split(" ").forEach(function(e) {
  var t = e.replace(Co, jo);
  pe[t] = new Se(t, 1, !1, e, "http://www.w3.org/1999/xlink", !1, !1);
});
["xml:base", "xml:lang", "xml:space"].forEach(function(e) {
  var t = e.replace(Co, jo);
  pe[t] = new Se(t, 1, !1, e, "http://www.w3.org/XML/1998/namespace", !1, !1);
});
["tabIndex", "crossOrigin"].forEach(function(e) {
  pe[e] = new Se(e, 1, !1, e.toLowerCase(), null, !1, !1);
});
pe.xlinkHref = new Se("xlinkHref", 1, !1, "xlink:href", "http://www.w3.org/1999/xlink", !0, !1);
["src", "href", "action", "formAction"].forEach(function(e) {
  pe[e] = new Se(e, 1, !1, e.toLowerCase(), null, !0, !0);
});
function Po(e, t, n, r) {
  var l = pe.hasOwnProperty(t) ? pe[t] : null;
  (l !== null ? l.type !== 0 : r || !(2 < t.length) || t[0] !== "o" && t[0] !== "O" || t[1] !== "n" && t[1] !== "N") && (Ld(t, n, l, r) && (n = null), r || l === null ? Pd(t) && (n === null ? e.removeAttribute(t) : e.setAttribute(t, "" + n)) : l.mustUseProperty ? e[l.propertyName] = n === null ? l.type === 3 ? !1 : "" : n : (t = l.attributeName, r = l.attributeNamespace, n === null ? e.removeAttribute(t) : (l = l.type, n = l === 3 || l === 4 && n === !0 ? "" : "" + n, r ? e.setAttributeNS(r, t, n) : e.setAttribute(t, n))));
}
var gt = Cd.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED, Rr = Symbol.for("react.element"), sn = Symbol.for("react.portal"), an = Symbol.for("react.fragment"), To = Symbol.for("react.strict_mode"), Ti = Symbol.for("react.profiler"), Gs = Symbol.for("react.provider"), Ys = Symbol.for("react.context"), Lo = Symbol.for("react.forward_ref"), Li = Symbol.for("react.suspense"), Wi = Symbol.for("react.suspense_list"), Wo = Symbol.for("react.memo"), St = Symbol.for("react.lazy"), Xs = Symbol.for("react.offscreen"), Nu = Symbol.iterator;
function $n(e) {
  return e === null || typeof e != "object" ? null : (e = Nu && e[Nu] || e["@@iterator"], typeof e == "function" ? e : null);
}
var b = Object.assign, ni;
function Zn(e) {
  if (ni === void 0) try {
    throw Error();
  } catch (n) {
    var t = n.stack.trim().match(/\n( *(at )?)/);
    ni = t && t[1] || "";
  }
  return `
` + ni + e;
}
var ri = !1;
function li(e, t) {
  if (!e || ri) return "";
  ri = !0;
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
      } catch (d) {
        var r = d;
      }
      Reflect.construct(e, [], t);
    } else {
      try {
        t.call();
      } catch (d) {
        r = d;
      }
      e.call(t.prototype);
    }
    else {
      try {
        throw Error();
      } catch (d) {
        r = d;
      }
      e();
    }
  } catch (d) {
    if (d && r && typeof d.stack == "string") {
      for (var l = d.stack.split(`
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
    ri = !1, Error.prepareStackTrace = n;
  }
  return (e = e ? e.displayName || e.name : "") ? Zn(e) : "";
}
function Wd(e) {
  switch (e.tag) {
    case 5:
      return Zn(e.type);
    case 16:
      return Zn("Lazy");
    case 13:
      return Zn("Suspense");
    case 19:
      return Zn("SuspenseList");
    case 0:
    case 2:
    case 15:
      return e = li(e.type, !1), e;
    case 11:
      return e = li(e.type.render, !1), e;
    case 1:
      return e = li(e.type, !0), e;
    default:
      return "";
  }
}
function Fi(e) {
  if (e == null) return null;
  if (typeof e == "function") return e.displayName || e.name || null;
  if (typeof e == "string") return e;
  switch (e) {
    case an:
      return "Fragment";
    case sn:
      return "Portal";
    case Ti:
      return "Profiler";
    case To:
      return "StrictMode";
    case Li:
      return "Suspense";
    case Wi:
      return "SuspenseList";
  }
  if (typeof e == "object") switch (e.$$typeof) {
    case Ys:
      return (e.displayName || "Context") + ".Consumer";
    case Gs:
      return (e._context.displayName || "Context") + ".Provider";
    case Lo:
      var t = e.render;
      return e = e.displayName, e || (e = t.displayName || t.name || "", e = e !== "" ? "ForwardRef(" + e + ")" : "ForwardRef"), e;
    case Wo:
      return t = e.displayName || null, t !== null ? t : Fi(e.type) || "Memo";
    case St:
      t = e._payload, e = e._init;
      try {
        return Fi(e(t));
      } catch {
      }
  }
  return null;
}
function Fd(e) {
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
      return Fi(t);
    case 8:
      return t === To ? "StrictMode" : "Mode";
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
function Ot(e) {
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
function Zs(e) {
  var t = e.type;
  return (e = e.nodeName) && e.toLowerCase() === "input" && (t === "checkbox" || t === "radio");
}
function Md(e) {
  var t = Zs(e) ? "checked" : "value", n = Object.getOwnPropertyDescriptor(e.constructor.prototype, t), r = "" + e[t];
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
function Dr(e) {
  e._valueTracker || (e._valueTracker = Md(e));
}
function Js(e) {
  if (!e) return !1;
  var t = e._valueTracker;
  if (!t) return !0;
  var n = t.getValue(), r = "";
  return e && (r = Zs(e) ? e.checked ? "true" : "false" : e.value), e = r, e !== n ? (t.setValue(e), !0) : !1;
}
function ml(e) {
  if (e = e || (typeof document < "u" ? document : void 0), typeof e > "u") return null;
  try {
    return e.activeElement || e.body;
  } catch {
    return e.body;
  }
}
function Mi(e, t) {
  var n = t.checked;
  return b({}, t, { defaultChecked: void 0, defaultValue: void 0, value: void 0, checked: n ?? e._wrapperState.initialChecked });
}
function Cu(e, t) {
  var n = t.defaultValue == null ? "" : t.defaultValue, r = t.checked != null ? t.checked : t.defaultChecked;
  n = Ot(t.value != null ? t.value : n), e._wrapperState = { initialChecked: r, initialValue: n, controlled: t.type === "checkbox" || t.type === "radio" ? t.checked != null : t.value != null };
}
function qs(e, t) {
  t = t.checked, t != null && Po(e, "checked", t, !1);
}
function Oi(e, t) {
  qs(e, t);
  var n = Ot(t.value), r = t.type;
  if (n != null) r === "number" ? (n === 0 && e.value === "" || e.value != n) && (e.value = "" + n) : e.value !== "" + n && (e.value = "" + n);
  else if (r === "submit" || r === "reset") {
    e.removeAttribute("value");
    return;
  }
  t.hasOwnProperty("value") ? Ai(e, t.type, n) : t.hasOwnProperty("defaultValue") && Ai(e, t.type, Ot(t.defaultValue)), t.checked == null && t.defaultChecked != null && (e.defaultChecked = !!t.defaultChecked);
}
function ju(e, t, n) {
  if (t.hasOwnProperty("value") || t.hasOwnProperty("defaultValue")) {
    var r = t.type;
    if (!(r !== "submit" && r !== "reset" || t.value !== void 0 && t.value !== null)) return;
    t = "" + e._wrapperState.initialValue, n || t === e.value || (e.value = t), e.defaultValue = t;
  }
  n = e.name, n !== "" && (e.name = ""), e.defaultChecked = !!e._wrapperState.initialChecked, n !== "" && (e.name = n);
}
function Ai(e, t, n) {
  (t !== "number" || ml(e.ownerDocument) !== e) && (n == null ? e.defaultValue = "" + e._wrapperState.initialValue : e.defaultValue !== "" + n && (e.defaultValue = "" + n));
}
var Jn = Array.isArray;
function kn(e, t, n, r) {
  if (e = e.options, t) {
    t = {};
    for (var l = 0; l < n.length; l++) t["$" + n[l]] = !0;
    for (n = 0; n < e.length; n++) l = t.hasOwnProperty("$" + e[n].value), e[n].selected !== l && (e[n].selected = l), l && r && (e[n].defaultSelected = !0);
  } else {
    for (n = "" + Ot(n), t = null, l = 0; l < e.length; l++) {
      if (e[l].value === n) {
        e[l].selected = !0, r && (e[l].defaultSelected = !0);
        return;
      }
      t !== null || e[l].disabled || (t = e[l]);
    }
    t !== null && (t.selected = !0);
  }
}
function Ri(e, t) {
  if (t.dangerouslySetInnerHTML != null) throw Error(w(91));
  return b({}, t, { value: void 0, defaultValue: void 0, children: "" + e._wrapperState.initialValue });
}
function Pu(e, t) {
  var n = t.value;
  if (n == null) {
    if (n = t.children, t = t.defaultValue, n != null) {
      if (t != null) throw Error(w(92));
      if (Jn(n)) {
        if (1 < n.length) throw Error(w(93));
        n = n[0];
      }
      t = n;
    }
    t == null && (t = ""), n = t;
  }
  e._wrapperState = { initialValue: Ot(n) };
}
function bs(e, t) {
  var n = Ot(t.value), r = Ot(t.defaultValue);
  n != null && (n = "" + n, n !== e.value && (e.value = n), t.defaultValue == null && e.defaultValue !== n && (e.defaultValue = n)), r != null && (e.defaultValue = "" + r);
}
function Tu(e) {
  var t = e.textContent;
  t === e._wrapperState.initialValue && t !== "" && t !== null && (e.value = t);
}
function ea(e) {
  switch (e) {
    case "svg":
      return "http://www.w3.org/2000/svg";
    case "math":
      return "http://www.w3.org/1998/Math/MathML";
    default:
      return "http://www.w3.org/1999/xhtml";
  }
}
function Di(e, t) {
  return e == null || e === "http://www.w3.org/1999/xhtml" ? ea(t) : e === "http://www.w3.org/2000/svg" && t === "foreignObject" ? "http://www.w3.org/1999/xhtml" : e;
}
var Ir, ta = function(e) {
  return typeof MSApp < "u" && MSApp.execUnsafeLocalFunction ? function(t, n, r, l) {
    MSApp.execUnsafeLocalFunction(function() {
      return e(t, n, r, l);
    });
  } : e;
}(function(e, t) {
  if (e.namespaceURI !== "http://www.w3.org/2000/svg" || "innerHTML" in e) e.innerHTML = t;
  else {
    for (Ir = Ir || document.createElement("div"), Ir.innerHTML = "<svg>" + t.valueOf().toString() + "</svg>", t = Ir.firstChild; e.firstChild; ) e.removeChild(e.firstChild);
    for (; t.firstChild; ) e.appendChild(t.firstChild);
  }
});
function dr(e, t) {
  if (t) {
    var n = e.firstChild;
    if (n && n === e.lastChild && n.nodeType === 3) {
      n.nodeValue = t;
      return;
    }
  }
  e.textContent = t;
}
var tr = {
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
}, Od = ["Webkit", "ms", "Moz", "O"];
Object.keys(tr).forEach(function(e) {
  Od.forEach(function(t) {
    t = t + e.charAt(0).toUpperCase() + e.substring(1), tr[t] = tr[e];
  });
});
function na(e, t, n) {
  return t == null || typeof t == "boolean" || t === "" ? "" : n || typeof t != "number" || t === 0 || tr.hasOwnProperty(e) && tr[e] ? ("" + t).trim() : t + "px";
}
function ra(e, t) {
  e = e.style;
  for (var n in t) if (t.hasOwnProperty(n)) {
    var r = n.indexOf("--") === 0, l = na(n, t[n], r);
    n === "float" && (n = "cssFloat"), r ? e.setProperty(n, l) : e[n] = l;
  }
}
var Ad = b({ menuitem: !0 }, { area: !0, base: !0, br: !0, col: !0, embed: !0, hr: !0, img: !0, input: !0, keygen: !0, link: !0, meta: !0, param: !0, source: !0, track: !0, wbr: !0 });
function Ii(e, t) {
  if (t) {
    if (Ad[e] && (t.children != null || t.dangerouslySetInnerHTML != null)) throw Error(w(137, e));
    if (t.dangerouslySetInnerHTML != null) {
      if (t.children != null) throw Error(w(60));
      if (typeof t.dangerouslySetInnerHTML != "object" || !("__html" in t.dangerouslySetInnerHTML)) throw Error(w(61));
    }
    if (t.style != null && typeof t.style != "object") throw Error(w(62));
  }
}
function Ui(e, t) {
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
var $i = null;
function Fo(e) {
  return e = e.target || e.srcElement || window, e.correspondingUseElement && (e = e.correspondingUseElement), e.nodeType === 3 ? e.parentNode : e;
}
var Vi = null, Sn = null, _n = null;
function Lu(e) {
  if (e = Tr(e)) {
    if (typeof Vi != "function") throw Error(w(280));
    var t = e.stateNode;
    t && (t = Kl(t), Vi(e.stateNode, e.type, t));
  }
}
function la(e) {
  Sn ? _n ? _n.push(e) : _n = [e] : Sn = e;
}
function ia() {
  if (Sn) {
    var e = Sn, t = _n;
    if (_n = Sn = null, Lu(e), t) for (e = 0; e < t.length; e++) Lu(t[e]);
  }
}
function oa(e, t) {
  return e(t);
}
function ua() {
}
var ii = !1;
function sa(e, t, n) {
  if (ii) return e(t, n);
  ii = !0;
  try {
    return oa(e, t, n);
  } finally {
    ii = !1, (Sn !== null || _n !== null) && (ua(), ia());
  }
}
function fr(e, t) {
  var n = e.stateNode;
  if (n === null) return null;
  var r = Kl(n);
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
  if (n && typeof n != "function") throw Error(w(231, t, typeof n));
  return n;
}
var Ki = !1;
if (ft) try {
  var Vn = {};
  Object.defineProperty(Vn, "passive", { get: function() {
    Ki = !0;
  } }), window.addEventListener("test", Vn, Vn), window.removeEventListener("test", Vn, Vn);
} catch {
  Ki = !1;
}
function Rd(e, t, n, r, l, i, o, u, s) {
  var d = Array.prototype.slice.call(arguments, 3);
  try {
    t.apply(n, d);
  } catch (m) {
    this.onError(m);
  }
}
var nr = !1, hl = null, gl = !1, Bi = null, Dd = { onError: function(e) {
  nr = !0, hl = e;
} };
function Id(e, t, n, r, l, i, o, u, s) {
  nr = !1, hl = null, Rd.apply(Dd, arguments);
}
function Ud(e, t, n, r, l, i, o, u, s) {
  if (Id.apply(this, arguments), nr) {
    if (nr) {
      var d = hl;
      nr = !1, hl = null;
    } else throw Error(w(198));
    gl || (gl = !0, Bi = d);
  }
}
function rn(e) {
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
function aa(e) {
  if (e.tag === 13) {
    var t = e.memoizedState;
    if (t === null && (e = e.alternate, e !== null && (t = e.memoizedState)), t !== null) return t.dehydrated;
  }
  return null;
}
function Wu(e) {
  if (rn(e) !== e) throw Error(w(188));
}
function $d(e) {
  var t = e.alternate;
  if (!t) {
    if (t = rn(e), t === null) throw Error(w(188));
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
        if (i === n) return Wu(l), e;
        if (i === r) return Wu(l), t;
        i = i.sibling;
      }
      throw Error(w(188));
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
        if (!o) throw Error(w(189));
      }
    }
    if (n.alternate !== r) throw Error(w(190));
  }
  if (n.tag !== 3) throw Error(w(188));
  return n.stateNode.current === n ? e : t;
}
function ca(e) {
  return e = $d(e), e !== null ? da(e) : null;
}
function da(e) {
  if (e.tag === 5 || e.tag === 6) return e;
  for (e = e.child; e !== null; ) {
    var t = da(e);
    if (t !== null) return t;
    e = e.sibling;
  }
  return null;
}
var fa = Fe.unstable_scheduleCallback, Fu = Fe.unstable_cancelCallback, Vd = Fe.unstable_shouldYield, Kd = Fe.unstable_requestPaint, ne = Fe.unstable_now, Bd = Fe.unstable_getCurrentPriorityLevel, Mo = Fe.unstable_ImmediatePriority, pa = Fe.unstable_UserBlockingPriority, vl = Fe.unstable_NormalPriority, Hd = Fe.unstable_LowPriority, ma = Fe.unstable_IdlePriority, Il = null, rt = null;
function Qd(e) {
  if (rt && typeof rt.onCommitFiberRoot == "function") try {
    rt.onCommitFiberRoot(Il, e, void 0, (e.current.flags & 128) === 128);
  } catch {
  }
}
var Ze = Math.clz32 ? Math.clz32 : Xd, Gd = Math.log, Yd = Math.LN2;
function Xd(e) {
  return e >>>= 0, e === 0 ? 32 : 31 - (Gd(e) / Yd | 0) | 0;
}
var Ur = 64, $r = 4194304;
function qn(e) {
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
function yl(e, t) {
  var n = e.pendingLanes;
  if (n === 0) return 0;
  var r = 0, l = e.suspendedLanes, i = e.pingedLanes, o = n & 268435455;
  if (o !== 0) {
    var u = o & ~l;
    u !== 0 ? r = qn(u) : (i &= o, i !== 0 && (r = qn(i)));
  } else o = n & ~l, o !== 0 ? r = qn(o) : i !== 0 && (r = qn(i));
  if (r === 0) return 0;
  if (t !== 0 && t !== r && !(t & l) && (l = r & -r, i = t & -t, l >= i || l === 16 && (i & 4194240) !== 0)) return t;
  if (r & 4 && (r |= n & 16), t = e.entangledLanes, t !== 0) for (e = e.entanglements, t &= r; 0 < t; ) n = 31 - Ze(t), l = 1 << n, r |= e[n], t &= ~l;
  return r;
}
function Zd(e, t) {
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
function Jd(e, t) {
  for (var n = e.suspendedLanes, r = e.pingedLanes, l = e.expirationTimes, i = e.pendingLanes; 0 < i; ) {
    var o = 31 - Ze(i), u = 1 << o, s = l[o];
    s === -1 ? (!(u & n) || u & r) && (l[o] = Zd(u, t)) : s <= t && (e.expiredLanes |= u), i &= ~u;
  }
}
function Hi(e) {
  return e = e.pendingLanes & -1073741825, e !== 0 ? e : e & 1073741824 ? 1073741824 : 0;
}
function ha() {
  var e = Ur;
  return Ur <<= 1, !(Ur & 4194240) && (Ur = 64), e;
}
function oi(e) {
  for (var t = [], n = 0; 31 > n; n++) t.push(e);
  return t;
}
function jr(e, t, n) {
  e.pendingLanes |= t, t !== 536870912 && (e.suspendedLanes = 0, e.pingedLanes = 0), e = e.eventTimes, t = 31 - Ze(t), e[t] = n;
}
function qd(e, t) {
  var n = e.pendingLanes & ~t;
  e.pendingLanes = t, e.suspendedLanes = 0, e.pingedLanes = 0, e.expiredLanes &= t, e.mutableReadLanes &= t, e.entangledLanes &= t, t = e.entanglements;
  var r = e.eventTimes;
  for (e = e.expirationTimes; 0 < n; ) {
    var l = 31 - Ze(n), i = 1 << l;
    t[l] = 0, r[l] = -1, e[l] = -1, n &= ~i;
  }
}
function Oo(e, t) {
  var n = e.entangledLanes |= t;
  for (e = e.entanglements; n; ) {
    var r = 31 - Ze(n), l = 1 << r;
    l & t | e[r] & t && (e[r] |= t), n &= ~l;
  }
}
var V = 0;
function ga(e) {
  return e &= -e, 1 < e ? 4 < e ? e & 268435455 ? 16 : 536870912 : 4 : 1;
}
var va, Ao, ya, wa, ka, Qi = !1, Vr = [], Ct = null, jt = null, Pt = null, pr = /* @__PURE__ */ new Map(), mr = /* @__PURE__ */ new Map(), xt = [], bd = "mousedown mouseup touchcancel touchend touchstart auxclick dblclick pointercancel pointerdown pointerup dragend dragstart drop compositionend compositionstart keydown keypress keyup input textInput copy cut paste click change contextmenu reset submit".split(" ");
function Mu(e, t) {
  switch (e) {
    case "focusin":
    case "focusout":
      Ct = null;
      break;
    case "dragenter":
    case "dragleave":
      jt = null;
      break;
    case "mouseover":
    case "mouseout":
      Pt = null;
      break;
    case "pointerover":
    case "pointerout":
      pr.delete(t.pointerId);
      break;
    case "gotpointercapture":
    case "lostpointercapture":
      mr.delete(t.pointerId);
  }
}
function Kn(e, t, n, r, l, i) {
  return e === null || e.nativeEvent !== i ? (e = { blockedOn: t, domEventName: n, eventSystemFlags: r, nativeEvent: i, targetContainers: [l] }, t !== null && (t = Tr(t), t !== null && Ao(t)), e) : (e.eventSystemFlags |= r, t = e.targetContainers, l !== null && t.indexOf(l) === -1 && t.push(l), e);
}
function ef(e, t, n, r, l) {
  switch (t) {
    case "focusin":
      return Ct = Kn(Ct, e, t, n, r, l), !0;
    case "dragenter":
      return jt = Kn(jt, e, t, n, r, l), !0;
    case "mouseover":
      return Pt = Kn(Pt, e, t, n, r, l), !0;
    case "pointerover":
      var i = l.pointerId;
      return pr.set(i, Kn(pr.get(i) || null, e, t, n, r, l)), !0;
    case "gotpointercapture":
      return i = l.pointerId, mr.set(i, Kn(mr.get(i) || null, e, t, n, r, l)), !0;
  }
  return !1;
}
function Sa(e) {
  var t = Qt(e.target);
  if (t !== null) {
    var n = rn(t);
    if (n !== null) {
      if (t = n.tag, t === 13) {
        if (t = aa(n), t !== null) {
          e.blockedOn = t, ka(e.priority, function() {
            ya(n);
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
function rl(e) {
  if (e.blockedOn !== null) return !1;
  for (var t = e.targetContainers; 0 < t.length; ) {
    var n = Gi(e.domEventName, e.eventSystemFlags, t[0], e.nativeEvent);
    if (n === null) {
      n = e.nativeEvent;
      var r = new n.constructor(n.type, n);
      $i = r, n.target.dispatchEvent(r), $i = null;
    } else return t = Tr(n), t !== null && Ao(t), e.blockedOn = n, !1;
    t.shift();
  }
  return !0;
}
function Ou(e, t, n) {
  rl(e) && n.delete(t);
}
function tf() {
  Qi = !1, Ct !== null && rl(Ct) && (Ct = null), jt !== null && rl(jt) && (jt = null), Pt !== null && rl(Pt) && (Pt = null), pr.forEach(Ou), mr.forEach(Ou);
}
function Bn(e, t) {
  e.blockedOn === t && (e.blockedOn = null, Qi || (Qi = !0, Fe.unstable_scheduleCallback(Fe.unstable_NormalPriority, tf)));
}
function hr(e) {
  function t(l) {
    return Bn(l, e);
  }
  if (0 < Vr.length) {
    Bn(Vr[0], e);
    for (var n = 1; n < Vr.length; n++) {
      var r = Vr[n];
      r.blockedOn === e && (r.blockedOn = null);
    }
  }
  for (Ct !== null && Bn(Ct, e), jt !== null && Bn(jt, e), Pt !== null && Bn(Pt, e), pr.forEach(t), mr.forEach(t), n = 0; n < xt.length; n++) r = xt[n], r.blockedOn === e && (r.blockedOn = null);
  for (; 0 < xt.length && (n = xt[0], n.blockedOn === null); ) Sa(n), n.blockedOn === null && xt.shift();
}
var xn = gt.ReactCurrentBatchConfig, wl = !0;
function nf(e, t, n, r) {
  var l = V, i = xn.transition;
  xn.transition = null;
  try {
    V = 1, Ro(e, t, n, r);
  } finally {
    V = l, xn.transition = i;
  }
}
function rf(e, t, n, r) {
  var l = V, i = xn.transition;
  xn.transition = null;
  try {
    V = 4, Ro(e, t, n, r);
  } finally {
    V = l, xn.transition = i;
  }
}
function Ro(e, t, n, r) {
  if (wl) {
    var l = Gi(e, t, n, r);
    if (l === null) gi(e, t, r, kl, n), Mu(e, r);
    else if (ef(l, e, t, n, r)) r.stopPropagation();
    else if (Mu(e, r), t & 4 && -1 < bd.indexOf(e)) {
      for (; l !== null; ) {
        var i = Tr(l);
        if (i !== null && va(i), i = Gi(e, t, n, r), i === null && gi(e, t, r, kl, n), i === l) break;
        l = i;
      }
      l !== null && r.stopPropagation();
    } else gi(e, t, r, null, n);
  }
}
var kl = null;
function Gi(e, t, n, r) {
  if (kl = null, e = Fo(r), e = Qt(e), e !== null) if (t = rn(e), t === null) e = null;
  else if (n = t.tag, n === 13) {
    if (e = aa(t), e !== null) return e;
    e = null;
  } else if (n === 3) {
    if (t.stateNode.current.memoizedState.isDehydrated) return t.tag === 3 ? t.stateNode.containerInfo : null;
    e = null;
  } else t !== e && (e = null);
  return kl = e, null;
}
function _a(e) {
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
      switch (Bd()) {
        case Mo:
          return 1;
        case pa:
          return 4;
        case vl:
        case Hd:
          return 16;
        case ma:
          return 536870912;
        default:
          return 16;
      }
    default:
      return 16;
  }
}
var Et = null, Do = null, ll = null;
function xa() {
  if (ll) return ll;
  var e, t = Do, n = t.length, r, l = "value" in Et ? Et.value : Et.textContent, i = l.length;
  for (e = 0; e < n && t[e] === l[e]; e++) ;
  var o = n - e;
  for (r = 1; r <= o && t[n - r] === l[i - r]; r++) ;
  return ll = l.slice(e, 1 < r ? 1 - r : void 0);
}
function il(e) {
  var t = e.keyCode;
  return "charCode" in e ? (e = e.charCode, e === 0 && t === 13 && (e = 13)) : e = t, e === 10 && (e = 13), 32 <= e || e === 13 ? e : 0;
}
function Kr() {
  return !0;
}
function Au() {
  return !1;
}
function Oe(e) {
  function t(n, r, l, i, o) {
    this._reactName = n, this._targetInst = l, this.type = r, this.nativeEvent = i, this.target = o, this.currentTarget = null;
    for (var u in e) e.hasOwnProperty(u) && (n = e[u], this[u] = n ? n(i) : i[u]);
    return this.isDefaultPrevented = (i.defaultPrevented != null ? i.defaultPrevented : i.returnValue === !1) ? Kr : Au, this.isPropagationStopped = Au, this;
  }
  return b(t.prototype, { preventDefault: function() {
    this.defaultPrevented = !0;
    var n = this.nativeEvent;
    n && (n.preventDefault ? n.preventDefault() : typeof n.returnValue != "unknown" && (n.returnValue = !1), this.isDefaultPrevented = Kr);
  }, stopPropagation: function() {
    var n = this.nativeEvent;
    n && (n.stopPropagation ? n.stopPropagation() : typeof n.cancelBubble != "unknown" && (n.cancelBubble = !0), this.isPropagationStopped = Kr);
  }, persist: function() {
  }, isPersistent: Kr }), t;
}
var Mn = { eventPhase: 0, bubbles: 0, cancelable: 0, timeStamp: function(e) {
  return e.timeStamp || Date.now();
}, defaultPrevented: 0, isTrusted: 0 }, Io = Oe(Mn), Pr = b({}, Mn, { view: 0, detail: 0 }), lf = Oe(Pr), ui, si, Hn, Ul = b({}, Pr, { screenX: 0, screenY: 0, clientX: 0, clientY: 0, pageX: 0, pageY: 0, ctrlKey: 0, shiftKey: 0, altKey: 0, metaKey: 0, getModifierState: Uo, button: 0, buttons: 0, relatedTarget: function(e) {
  return e.relatedTarget === void 0 ? e.fromElement === e.srcElement ? e.toElement : e.fromElement : e.relatedTarget;
}, movementX: function(e) {
  return "movementX" in e ? e.movementX : (e !== Hn && (Hn && e.type === "mousemove" ? (ui = e.screenX - Hn.screenX, si = e.screenY - Hn.screenY) : si = ui = 0, Hn = e), ui);
}, movementY: function(e) {
  return "movementY" in e ? e.movementY : si;
} }), Ru = Oe(Ul), of = b({}, Ul, { dataTransfer: 0 }), uf = Oe(of), sf = b({}, Pr, { relatedTarget: 0 }), ai = Oe(sf), af = b({}, Mn, { animationName: 0, elapsedTime: 0, pseudoElement: 0 }), cf = Oe(af), df = b({}, Mn, { clipboardData: function(e) {
  return "clipboardData" in e ? e.clipboardData : window.clipboardData;
} }), ff = Oe(df), pf = b({}, Mn, { data: 0 }), Du = Oe(pf), mf = {
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
}, hf = {
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
}, gf = { Alt: "altKey", Control: "ctrlKey", Meta: "metaKey", Shift: "shiftKey" };
function vf(e) {
  var t = this.nativeEvent;
  return t.getModifierState ? t.getModifierState(e) : (e = gf[e]) ? !!t[e] : !1;
}
function Uo() {
  return vf;
}
var yf = b({}, Pr, { key: function(e) {
  if (e.key) {
    var t = mf[e.key] || e.key;
    if (t !== "Unidentified") return t;
  }
  return e.type === "keypress" ? (e = il(e), e === 13 ? "Enter" : String.fromCharCode(e)) : e.type === "keydown" || e.type === "keyup" ? hf[e.keyCode] || "Unidentified" : "";
}, code: 0, location: 0, ctrlKey: 0, shiftKey: 0, altKey: 0, metaKey: 0, repeat: 0, locale: 0, getModifierState: Uo, charCode: function(e) {
  return e.type === "keypress" ? il(e) : 0;
}, keyCode: function(e) {
  return e.type === "keydown" || e.type === "keyup" ? e.keyCode : 0;
}, which: function(e) {
  return e.type === "keypress" ? il(e) : e.type === "keydown" || e.type === "keyup" ? e.keyCode : 0;
} }), wf = Oe(yf), kf = b({}, Ul, { pointerId: 0, width: 0, height: 0, pressure: 0, tangentialPressure: 0, tiltX: 0, tiltY: 0, twist: 0, pointerType: 0, isPrimary: 0 }), Iu = Oe(kf), Sf = b({}, Pr, { touches: 0, targetTouches: 0, changedTouches: 0, altKey: 0, metaKey: 0, ctrlKey: 0, shiftKey: 0, getModifierState: Uo }), _f = Oe(Sf), xf = b({}, Mn, { propertyName: 0, elapsedTime: 0, pseudoElement: 0 }), zf = Oe(xf), Ef = b({}, Ul, {
  deltaX: function(e) {
    return "deltaX" in e ? e.deltaX : "wheelDeltaX" in e ? -e.wheelDeltaX : 0;
  },
  deltaY: function(e) {
    return "deltaY" in e ? e.deltaY : "wheelDeltaY" in e ? -e.wheelDeltaY : "wheelDelta" in e ? -e.wheelDelta : 0;
  },
  deltaZ: 0,
  deltaMode: 0
}), Nf = Oe(Ef), Cf = [9, 13, 27, 32], $o = ft && "CompositionEvent" in window, rr = null;
ft && "documentMode" in document && (rr = document.documentMode);
var jf = ft && "TextEvent" in window && !rr, za = ft && (!$o || rr && 8 < rr && 11 >= rr), Uu = " ", $u = !1;
function Ea(e, t) {
  switch (e) {
    case "keyup":
      return Cf.indexOf(t.keyCode) !== -1;
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
function Na(e) {
  return e = e.detail, typeof e == "object" && "data" in e ? e.data : null;
}
var cn = !1;
function Pf(e, t) {
  switch (e) {
    case "compositionend":
      return Na(t);
    case "keypress":
      return t.which !== 32 ? null : ($u = !0, Uu);
    case "textInput":
      return e = t.data, e === Uu && $u ? null : e;
    default:
      return null;
  }
}
function Tf(e, t) {
  if (cn) return e === "compositionend" || !$o && Ea(e, t) ? (e = xa(), ll = Do = Et = null, cn = !1, e) : null;
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
      return za && t.locale !== "ko" ? null : t.data;
    default:
      return null;
  }
}
var Lf = { color: !0, date: !0, datetime: !0, "datetime-local": !0, email: !0, month: !0, number: !0, password: !0, range: !0, search: !0, tel: !0, text: !0, time: !0, url: !0, week: !0 };
function Vu(e) {
  var t = e && e.nodeName && e.nodeName.toLowerCase();
  return t === "input" ? !!Lf[e.type] : t === "textarea";
}
function Ca(e, t, n, r) {
  la(r), t = Sl(t, "onChange"), 0 < t.length && (n = new Io("onChange", "change", null, n, r), e.push({ event: n, listeners: t }));
}
var lr = null, gr = null;
function Wf(e) {
  Da(e, 0);
}
function $l(e) {
  var t = pn(e);
  if (Js(t)) return e;
}
function Ff(e, t) {
  if (e === "change") return t;
}
var ja = !1;
if (ft) {
  var ci;
  if (ft) {
    var di = "oninput" in document;
    if (!di) {
      var Ku = document.createElement("div");
      Ku.setAttribute("oninput", "return;"), di = typeof Ku.oninput == "function";
    }
    ci = di;
  } else ci = !1;
  ja = ci && (!document.documentMode || 9 < document.documentMode);
}
function Bu() {
  lr && (lr.detachEvent("onpropertychange", Pa), gr = lr = null);
}
function Pa(e) {
  if (e.propertyName === "value" && $l(gr)) {
    var t = [];
    Ca(t, gr, e, Fo(e)), sa(Wf, t);
  }
}
function Mf(e, t, n) {
  e === "focusin" ? (Bu(), lr = t, gr = n, lr.attachEvent("onpropertychange", Pa)) : e === "focusout" && Bu();
}
function Of(e) {
  if (e === "selectionchange" || e === "keyup" || e === "keydown") return $l(gr);
}
function Af(e, t) {
  if (e === "click") return $l(t);
}
function Rf(e, t) {
  if (e === "input" || e === "change") return $l(t);
}
function Df(e, t) {
  return e === t && (e !== 0 || 1 / e === 1 / t) || e !== e && t !== t;
}
var qe = typeof Object.is == "function" ? Object.is : Df;
function vr(e, t) {
  if (qe(e, t)) return !0;
  if (typeof e != "object" || e === null || typeof t != "object" || t === null) return !1;
  var n = Object.keys(e), r = Object.keys(t);
  if (n.length !== r.length) return !1;
  for (r = 0; r < n.length; r++) {
    var l = n[r];
    if (!Pi.call(t, l) || !qe(e[l], t[l])) return !1;
  }
  return !0;
}
function Hu(e) {
  for (; e && e.firstChild; ) e = e.firstChild;
  return e;
}
function Qu(e, t) {
  var n = Hu(e);
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
    n = Hu(n);
  }
}
function Ta(e, t) {
  return e && t ? e === t ? !0 : e && e.nodeType === 3 ? !1 : t && t.nodeType === 3 ? Ta(e, t.parentNode) : "contains" in e ? e.contains(t) : e.compareDocumentPosition ? !!(e.compareDocumentPosition(t) & 16) : !1 : !1;
}
function La() {
  for (var e = window, t = ml(); t instanceof e.HTMLIFrameElement; ) {
    try {
      var n = typeof t.contentWindow.location.href == "string";
    } catch {
      n = !1;
    }
    if (n) e = t.contentWindow;
    else break;
    t = ml(e.document);
  }
  return t;
}
function Vo(e) {
  var t = e && e.nodeName && e.nodeName.toLowerCase();
  return t && (t === "input" && (e.type === "text" || e.type === "search" || e.type === "tel" || e.type === "url" || e.type === "password") || t === "textarea" || e.contentEditable === "true");
}
function If(e) {
  var t = La(), n = e.focusedElem, r = e.selectionRange;
  if (t !== n && n && n.ownerDocument && Ta(n.ownerDocument.documentElement, n)) {
    if (r !== null && Vo(n)) {
      if (t = r.start, e = r.end, e === void 0 && (e = t), "selectionStart" in n) n.selectionStart = t, n.selectionEnd = Math.min(e, n.value.length);
      else if (e = (t = n.ownerDocument || document) && t.defaultView || window, e.getSelection) {
        e = e.getSelection();
        var l = n.textContent.length, i = Math.min(r.start, l);
        r = r.end === void 0 ? i : Math.min(r.end, l), !e.extend && i > r && (l = r, r = i, i = l), l = Qu(n, i);
        var o = Qu(
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
var Uf = ft && "documentMode" in document && 11 >= document.documentMode, dn = null, Yi = null, ir = null, Xi = !1;
function Gu(e, t, n) {
  var r = n.window === n ? n.document : n.nodeType === 9 ? n : n.ownerDocument;
  Xi || dn == null || dn !== ml(r) || (r = dn, "selectionStart" in r && Vo(r) ? r = { start: r.selectionStart, end: r.selectionEnd } : (r = (r.ownerDocument && r.ownerDocument.defaultView || window).getSelection(), r = { anchorNode: r.anchorNode, anchorOffset: r.anchorOffset, focusNode: r.focusNode, focusOffset: r.focusOffset }), ir && vr(ir, r) || (ir = r, r = Sl(Yi, "onSelect"), 0 < r.length && (t = new Io("onSelect", "select", null, t, n), e.push({ event: t, listeners: r }), t.target = dn)));
}
function Br(e, t) {
  var n = {};
  return n[e.toLowerCase()] = t.toLowerCase(), n["Webkit" + e] = "webkit" + t, n["Moz" + e] = "moz" + t, n;
}
var fn = { animationend: Br("Animation", "AnimationEnd"), animationiteration: Br("Animation", "AnimationIteration"), animationstart: Br("Animation", "AnimationStart"), transitionend: Br("Transition", "TransitionEnd") }, fi = {}, Wa = {};
ft && (Wa = document.createElement("div").style, "AnimationEvent" in window || (delete fn.animationend.animation, delete fn.animationiteration.animation, delete fn.animationstart.animation), "TransitionEvent" in window || delete fn.transitionend.transition);
function Vl(e) {
  if (fi[e]) return fi[e];
  if (!fn[e]) return e;
  var t = fn[e], n;
  for (n in t) if (t.hasOwnProperty(n) && n in Wa) return fi[e] = t[n];
  return e;
}
var Fa = Vl("animationend"), Ma = Vl("animationiteration"), Oa = Vl("animationstart"), Aa = Vl("transitionend"), Ra = /* @__PURE__ */ new Map(), Yu = "abort auxClick cancel canPlay canPlayThrough click close contextMenu copy cut drag dragEnd dragEnter dragExit dragLeave dragOver dragStart drop durationChange emptied encrypted ended error gotPointerCapture input invalid keyDown keyPress keyUp load loadedData loadedMetadata loadStart lostPointerCapture mouseDown mouseMove mouseOut mouseOver mouseUp paste pause play playing pointerCancel pointerDown pointerMove pointerOut pointerOver pointerUp progress rateChange reset resize seeked seeking stalled submit suspend timeUpdate touchCancel touchEnd touchStart volumeChange scroll toggle touchMove waiting wheel".split(" ");
function Rt(e, t) {
  Ra.set(e, t), nn(t, [e]);
}
for (var pi = 0; pi < Yu.length; pi++) {
  var mi = Yu[pi], $f = mi.toLowerCase(), Vf = mi[0].toUpperCase() + mi.slice(1);
  Rt($f, "on" + Vf);
}
Rt(Fa, "onAnimationEnd");
Rt(Ma, "onAnimationIteration");
Rt(Oa, "onAnimationStart");
Rt("dblclick", "onDoubleClick");
Rt("focusin", "onFocus");
Rt("focusout", "onBlur");
Rt(Aa, "onTransitionEnd");
Nn("onMouseEnter", ["mouseout", "mouseover"]);
Nn("onMouseLeave", ["mouseout", "mouseover"]);
Nn("onPointerEnter", ["pointerout", "pointerover"]);
Nn("onPointerLeave", ["pointerout", "pointerover"]);
nn("onChange", "change click focusin focusout input keydown keyup selectionchange".split(" "));
nn("onSelect", "focusout contextmenu dragend focusin keydown keyup mousedown mouseup selectionchange".split(" "));
nn("onBeforeInput", ["compositionend", "keypress", "textInput", "paste"]);
nn("onCompositionEnd", "compositionend focusout keydown keypress keyup mousedown".split(" "));
nn("onCompositionStart", "compositionstart focusout keydown keypress keyup mousedown".split(" "));
nn("onCompositionUpdate", "compositionupdate focusout keydown keypress keyup mousedown".split(" "));
var bn = "abort canplay canplaythrough durationchange emptied encrypted ended error loadeddata loadedmetadata loadstart pause play playing progress ratechange resize seeked seeking stalled suspend timeupdate volumechange waiting".split(" "), Kf = new Set("cancel close invalid load scroll toggle".split(" ").concat(bn));
function Xu(e, t, n) {
  var r = e.type || "unknown-event";
  e.currentTarget = n, Ud(r, t, void 0, e), e.currentTarget = null;
}
function Da(e, t) {
  t = (t & 4) !== 0;
  for (var n = 0; n < e.length; n++) {
    var r = e[n], l = r.event;
    r = r.listeners;
    e: {
      var i = void 0;
      if (t) for (var o = r.length - 1; 0 <= o; o--) {
        var u = r[o], s = u.instance, d = u.currentTarget;
        if (u = u.listener, s !== i && l.isPropagationStopped()) break e;
        Xu(l, u, d), i = s;
      }
      else for (o = 0; o < r.length; o++) {
        if (u = r[o], s = u.instance, d = u.currentTarget, u = u.listener, s !== i && l.isPropagationStopped()) break e;
        Xu(l, u, d), i = s;
      }
    }
  }
  if (gl) throw e = Bi, gl = !1, Bi = null, e;
}
function Y(e, t) {
  var n = t[eo];
  n === void 0 && (n = t[eo] = /* @__PURE__ */ new Set());
  var r = e + "__bubble";
  n.has(r) || (Ia(t, e, 2, !1), n.add(r));
}
function hi(e, t, n) {
  var r = 0;
  t && (r |= 4), Ia(n, e, r, t);
}
var Hr = "_reactListening" + Math.random().toString(36).slice(2);
function yr(e) {
  if (!e[Hr]) {
    e[Hr] = !0, Qs.forEach(function(n) {
      n !== "selectionchange" && (Kf.has(n) || hi(n, !1, e), hi(n, !0, e));
    });
    var t = e.nodeType === 9 ? e : e.ownerDocument;
    t === null || t[Hr] || (t[Hr] = !0, hi("selectionchange", !1, t));
  }
}
function Ia(e, t, n, r) {
  switch (_a(t)) {
    case 1:
      var l = nf;
      break;
    case 4:
      l = rf;
      break;
    default:
      l = Ro;
  }
  n = l.bind(null, t, n, e), l = void 0, !Ki || t !== "touchstart" && t !== "touchmove" && t !== "wheel" || (l = !0), r ? l !== void 0 ? e.addEventListener(t, n, { capture: !0, passive: l }) : e.addEventListener(t, n, !0) : l !== void 0 ? e.addEventListener(t, n, { passive: l }) : e.addEventListener(t, n, !1);
}
function gi(e, t, n, r, l) {
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
        if (o = Qt(u), o === null) return;
        if (s = o.tag, s === 5 || s === 6) {
          r = i = o;
          continue e;
        }
        u = u.parentNode;
      }
    }
    r = r.return;
  }
  sa(function() {
    var d = i, m = Fo(n), g = [];
    e: {
      var h = Ra.get(e);
      if (h !== void 0) {
        var S = Io, z = e;
        switch (e) {
          case "keypress":
            if (il(n) === 0) break e;
          case "keydown":
          case "keyup":
            S = wf;
            break;
          case "focusin":
            z = "focus", S = ai;
            break;
          case "focusout":
            z = "blur", S = ai;
            break;
          case "beforeblur":
          case "afterblur":
            S = ai;
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
            S = Ru;
            break;
          case "drag":
          case "dragend":
          case "dragenter":
          case "dragexit":
          case "dragleave":
          case "dragover":
          case "dragstart":
          case "drop":
            S = uf;
            break;
          case "touchcancel":
          case "touchend":
          case "touchmove":
          case "touchstart":
            S = _f;
            break;
          case Fa:
          case Ma:
          case Oa:
            S = cf;
            break;
          case Aa:
            S = zf;
            break;
          case "scroll":
            S = lf;
            break;
          case "wheel":
            S = Nf;
            break;
          case "copy":
          case "cut":
          case "paste":
            S = ff;
            break;
          case "gotpointercapture":
          case "lostpointercapture":
          case "pointercancel":
          case "pointerdown":
          case "pointermove":
          case "pointerout":
          case "pointerover":
          case "pointerup":
            S = Iu;
        }
        var _ = (t & 4) !== 0, B = !_ && e === "scroll", f = _ ? h !== null ? h + "Capture" : null : h;
        _ = [];
        for (var c = d, p; c !== null; ) {
          p = c;
          var y = p.stateNode;
          if (p.tag === 5 && y !== null && (p = y, f !== null && (y = fr(c, f), y != null && _.push(wr(c, y, p)))), B) break;
          c = c.return;
        }
        0 < _.length && (h = new S(h, z, null, n, m), g.push({ event: h, listeners: _ }));
      }
    }
    if (!(t & 7)) {
      e: {
        if (h = e === "mouseover" || e === "pointerover", S = e === "mouseout" || e === "pointerout", h && n !== $i && (z = n.relatedTarget || n.fromElement) && (Qt(z) || z[pt])) break e;
        if ((S || h) && (h = m.window === m ? m : (h = m.ownerDocument) ? h.defaultView || h.parentWindow : window, S ? (z = n.relatedTarget || n.toElement, S = d, z = z ? Qt(z) : null, z !== null && (B = rn(z), z !== B || z.tag !== 5 && z.tag !== 6) && (z = null)) : (S = null, z = d), S !== z)) {
          if (_ = Ru, y = "onMouseLeave", f = "onMouseEnter", c = "mouse", (e === "pointerout" || e === "pointerover") && (_ = Iu, y = "onPointerLeave", f = "onPointerEnter", c = "pointer"), B = S == null ? h : pn(S), p = z == null ? h : pn(z), h = new _(y, c + "leave", S, n, m), h.target = B, h.relatedTarget = p, y = null, Qt(m) === d && (_ = new _(f, c + "enter", z, n, m), _.target = p, _.relatedTarget = B, y = _), B = y, S && z) t: {
            for (_ = S, f = z, c = 0, p = _; p; p = un(p)) c++;
            for (p = 0, y = f; y; y = un(y)) p++;
            for (; 0 < c - p; ) _ = un(_), c--;
            for (; 0 < p - c; ) f = un(f), p--;
            for (; c--; ) {
              if (_ === f || f !== null && _ === f.alternate) break t;
              _ = un(_), f = un(f);
            }
            _ = null;
          }
          else _ = null;
          S !== null && Zu(g, h, S, _, !1), z !== null && B !== null && Zu(g, B, z, _, !0);
        }
      }
      e: {
        if (h = d ? pn(d) : window, S = h.nodeName && h.nodeName.toLowerCase(), S === "select" || S === "input" && h.type === "file") var C = Ff;
        else if (Vu(h)) if (ja) C = Rf;
        else {
          C = Of;
          var N = Mf;
        }
        else (S = h.nodeName) && S.toLowerCase() === "input" && (h.type === "checkbox" || h.type === "radio") && (C = Af);
        if (C && (C = C(e, d))) {
          Ca(g, C, n, m);
          break e;
        }
        N && N(e, h, d), e === "focusout" && (N = h._wrapperState) && N.controlled && h.type === "number" && Ai(h, "number", h.value);
      }
      switch (N = d ? pn(d) : window, e) {
        case "focusin":
          (Vu(N) || N.contentEditable === "true") && (dn = N, Yi = d, ir = null);
          break;
        case "focusout":
          ir = Yi = dn = null;
          break;
        case "mousedown":
          Xi = !0;
          break;
        case "contextmenu":
        case "mouseup":
        case "dragend":
          Xi = !1, Gu(g, n, m);
          break;
        case "selectionchange":
          if (Uf) break;
        case "keydown":
        case "keyup":
          Gu(g, n, m);
      }
      var P;
      if ($o) e: {
        switch (e) {
          case "compositionstart":
            var T = "onCompositionStart";
            break e;
          case "compositionend":
            T = "onCompositionEnd";
            break e;
          case "compositionupdate":
            T = "onCompositionUpdate";
            break e;
        }
        T = void 0;
      }
      else cn ? Ea(e, n) && (T = "onCompositionEnd") : e === "keydown" && n.keyCode === 229 && (T = "onCompositionStart");
      T && (za && n.locale !== "ko" && (cn || T !== "onCompositionStart" ? T === "onCompositionEnd" && cn && (P = xa()) : (Et = m, Do = "value" in Et ? Et.value : Et.textContent, cn = !0)), N = Sl(d, T), 0 < N.length && (T = new Du(T, e, null, n, m), g.push({ event: T, listeners: N }), P ? T.data = P : (P = Na(n), P !== null && (T.data = P)))), (P = jf ? Pf(e, n) : Tf(e, n)) && (d = Sl(d, "onBeforeInput"), 0 < d.length && (m = new Du("onBeforeInput", "beforeinput", null, n, m), g.push({ event: m, listeners: d }), m.data = P));
    }
    Da(g, t);
  });
}
function wr(e, t, n) {
  return { instance: e, listener: t, currentTarget: n };
}
function Sl(e, t) {
  for (var n = t + "Capture", r = []; e !== null; ) {
    var l = e, i = l.stateNode;
    l.tag === 5 && i !== null && (l = i, i = fr(e, n), i != null && r.unshift(wr(e, i, l)), i = fr(e, t), i != null && r.push(wr(e, i, l))), e = e.return;
  }
  return r;
}
function un(e) {
  if (e === null) return null;
  do
    e = e.return;
  while (e && e.tag !== 5);
  return e || null;
}
function Zu(e, t, n, r, l) {
  for (var i = t._reactName, o = []; n !== null && n !== r; ) {
    var u = n, s = u.alternate, d = u.stateNode;
    if (s !== null && s === r) break;
    u.tag === 5 && d !== null && (u = d, l ? (s = fr(n, i), s != null && o.unshift(wr(n, s, u))) : l || (s = fr(n, i), s != null && o.push(wr(n, s, u)))), n = n.return;
  }
  o.length !== 0 && e.push({ event: t, listeners: o });
}
var Bf = /\r\n?/g, Hf = /\u0000|\uFFFD/g;
function Ju(e) {
  return (typeof e == "string" ? e : "" + e).replace(Bf, `
`).replace(Hf, "");
}
function Qr(e, t, n) {
  if (t = Ju(t), Ju(e) !== t && n) throw Error(w(425));
}
function _l() {
}
var Zi = null, Ji = null;
function qi(e, t) {
  return e === "textarea" || e === "noscript" || typeof t.children == "string" || typeof t.children == "number" || typeof t.dangerouslySetInnerHTML == "object" && t.dangerouslySetInnerHTML !== null && t.dangerouslySetInnerHTML.__html != null;
}
var bi = typeof setTimeout == "function" ? setTimeout : void 0, Qf = typeof clearTimeout == "function" ? clearTimeout : void 0, qu = typeof Promise == "function" ? Promise : void 0, Gf = typeof queueMicrotask == "function" ? queueMicrotask : typeof qu < "u" ? function(e) {
  return qu.resolve(null).then(e).catch(Yf);
} : bi;
function Yf(e) {
  setTimeout(function() {
    throw e;
  });
}
function vi(e, t) {
  var n = t, r = 0;
  do {
    var l = n.nextSibling;
    if (e.removeChild(n), l && l.nodeType === 8) if (n = l.data, n === "/$") {
      if (r === 0) {
        e.removeChild(l), hr(t);
        return;
      }
      r--;
    } else n !== "$" && n !== "$?" && n !== "$!" || r++;
    n = l;
  } while (n);
  hr(t);
}
function Tt(e) {
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
function bu(e) {
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
var On = Math.random().toString(36).slice(2), nt = "__reactFiber$" + On, kr = "__reactProps$" + On, pt = "__reactContainer$" + On, eo = "__reactEvents$" + On, Xf = "__reactListeners$" + On, Zf = "__reactHandles$" + On;
function Qt(e) {
  var t = e[nt];
  if (t) return t;
  for (var n = e.parentNode; n; ) {
    if (t = n[pt] || n[nt]) {
      if (n = t.alternate, t.child !== null || n !== null && n.child !== null) for (e = bu(e); e !== null; ) {
        if (n = e[nt]) return n;
        e = bu(e);
      }
      return t;
    }
    e = n, n = e.parentNode;
  }
  return null;
}
function Tr(e) {
  return e = e[nt] || e[pt], !e || e.tag !== 5 && e.tag !== 6 && e.tag !== 13 && e.tag !== 3 ? null : e;
}
function pn(e) {
  if (e.tag === 5 || e.tag === 6) return e.stateNode;
  throw Error(w(33));
}
function Kl(e) {
  return e[kr] || null;
}
var to = [], mn = -1;
function Dt(e) {
  return { current: e };
}
function X(e) {
  0 > mn || (e.current = to[mn], to[mn] = null, mn--);
}
function G(e, t) {
  mn++, to[mn] = e.current, e.current = t;
}
var At = {}, ve = Dt(At), Ee = Dt(!1), Jt = At;
function Cn(e, t) {
  var n = e.type.contextTypes;
  if (!n) return At;
  var r = e.stateNode;
  if (r && r.__reactInternalMemoizedUnmaskedChildContext === t) return r.__reactInternalMemoizedMaskedChildContext;
  var l = {}, i;
  for (i in n) l[i] = t[i];
  return r && (e = e.stateNode, e.__reactInternalMemoizedUnmaskedChildContext = t, e.__reactInternalMemoizedMaskedChildContext = l), l;
}
function Ne(e) {
  return e = e.childContextTypes, e != null;
}
function xl() {
  X(Ee), X(ve);
}
function es(e, t, n) {
  if (ve.current !== At) throw Error(w(168));
  G(ve, t), G(Ee, n);
}
function Ua(e, t, n) {
  var r = e.stateNode;
  if (t = t.childContextTypes, typeof r.getChildContext != "function") return n;
  r = r.getChildContext();
  for (var l in r) if (!(l in t)) throw Error(w(108, Fd(e) || "Unknown", l));
  return b({}, n, r);
}
function zl(e) {
  return e = (e = e.stateNode) && e.__reactInternalMemoizedMergedChildContext || At, Jt = ve.current, G(ve, e), G(Ee, Ee.current), !0;
}
function ts(e, t, n) {
  var r = e.stateNode;
  if (!r) throw Error(w(169));
  n ? (e = Ua(e, t, Jt), r.__reactInternalMemoizedMergedChildContext = e, X(Ee), X(ve), G(ve, e)) : X(Ee), G(Ee, n);
}
var st = null, Bl = !1, yi = !1;
function $a(e) {
  st === null ? st = [e] : st.push(e);
}
function Jf(e) {
  Bl = !0, $a(e);
}
function It() {
  if (!yi && st !== null) {
    yi = !0;
    var e = 0, t = V;
    try {
      var n = st;
      for (V = 1; e < n.length; e++) {
        var r = n[e];
        do
          r = r(!0);
        while (r !== null);
      }
      st = null, Bl = !1;
    } catch (l) {
      throw st !== null && (st = st.slice(e + 1)), fa(Mo, It), l;
    } finally {
      V = t, yi = !1;
    }
  }
  return null;
}
var hn = [], gn = 0, El = null, Nl = 0, De = [], Ie = 0, qt = null, at = 1, ct = "";
function Kt(e, t) {
  hn[gn++] = Nl, hn[gn++] = El, El = e, Nl = t;
}
function Va(e, t, n) {
  De[Ie++] = at, De[Ie++] = ct, De[Ie++] = qt, qt = e;
  var r = at;
  e = ct;
  var l = 32 - Ze(r) - 1;
  r &= ~(1 << l), n += 1;
  var i = 32 - Ze(t) + l;
  if (30 < i) {
    var o = l - l % 5;
    i = (r & (1 << o) - 1).toString(32), r >>= o, l -= o, at = 1 << 32 - Ze(t) + l | n << l | r, ct = i + e;
  } else at = 1 << i | n << l | r, ct = e;
}
function Ko(e) {
  e.return !== null && (Kt(e, 1), Va(e, 1, 0));
}
function Bo(e) {
  for (; e === El; ) El = hn[--gn], hn[gn] = null, Nl = hn[--gn], hn[gn] = null;
  for (; e === qt; ) qt = De[--Ie], De[Ie] = null, ct = De[--Ie], De[Ie] = null, at = De[--Ie], De[Ie] = null;
}
var We = null, Te = null, Z = !1, Xe = null;
function Ka(e, t) {
  var n = Ue(5, null, null, 0);
  n.elementType = "DELETED", n.stateNode = t, n.return = e, t = e.deletions, t === null ? (e.deletions = [n], e.flags |= 16) : t.push(n);
}
function ns(e, t) {
  switch (e.tag) {
    case 5:
      var n = e.type;
      return t = t.nodeType !== 1 || n.toLowerCase() !== t.nodeName.toLowerCase() ? null : t, t !== null ? (e.stateNode = t, We = e, Te = Tt(t.firstChild), !0) : !1;
    case 6:
      return t = e.pendingProps === "" || t.nodeType !== 3 ? null : t, t !== null ? (e.stateNode = t, We = e, Te = null, !0) : !1;
    case 13:
      return t = t.nodeType !== 8 ? null : t, t !== null ? (n = qt !== null ? { id: at, overflow: ct } : null, e.memoizedState = { dehydrated: t, treeContext: n, retryLane: 1073741824 }, n = Ue(18, null, null, 0), n.stateNode = t, n.return = e, e.child = n, We = e, Te = null, !0) : !1;
    default:
      return !1;
  }
}
function no(e) {
  return (e.mode & 1) !== 0 && (e.flags & 128) === 0;
}
function ro(e) {
  if (Z) {
    var t = Te;
    if (t) {
      var n = t;
      if (!ns(e, t)) {
        if (no(e)) throw Error(w(418));
        t = Tt(n.nextSibling);
        var r = We;
        t && ns(e, t) ? Ka(r, n) : (e.flags = e.flags & -4097 | 2, Z = !1, We = e);
      }
    } else {
      if (no(e)) throw Error(w(418));
      e.flags = e.flags & -4097 | 2, Z = !1, We = e;
    }
  }
}
function rs(e) {
  for (e = e.return; e !== null && e.tag !== 5 && e.tag !== 3 && e.tag !== 13; ) e = e.return;
  We = e;
}
function Gr(e) {
  if (e !== We) return !1;
  if (!Z) return rs(e), Z = !0, !1;
  var t;
  if ((t = e.tag !== 3) && !(t = e.tag !== 5) && (t = e.type, t = t !== "head" && t !== "body" && !qi(e.type, e.memoizedProps)), t && (t = Te)) {
    if (no(e)) throw Ba(), Error(w(418));
    for (; t; ) Ka(e, t), t = Tt(t.nextSibling);
  }
  if (rs(e), e.tag === 13) {
    if (e = e.memoizedState, e = e !== null ? e.dehydrated : null, !e) throw Error(w(317));
    e: {
      for (e = e.nextSibling, t = 0; e; ) {
        if (e.nodeType === 8) {
          var n = e.data;
          if (n === "/$") {
            if (t === 0) {
              Te = Tt(e.nextSibling);
              break e;
            }
            t--;
          } else n !== "$" && n !== "$!" && n !== "$?" || t++;
        }
        e = e.nextSibling;
      }
      Te = null;
    }
  } else Te = We ? Tt(e.stateNode.nextSibling) : null;
  return !0;
}
function Ba() {
  for (var e = Te; e; ) e = Tt(e.nextSibling);
}
function jn() {
  Te = We = null, Z = !1;
}
function Ho(e) {
  Xe === null ? Xe = [e] : Xe.push(e);
}
var qf = gt.ReactCurrentBatchConfig;
function Qn(e, t, n) {
  if (e = n.ref, e !== null && typeof e != "function" && typeof e != "object") {
    if (n._owner) {
      if (n = n._owner, n) {
        if (n.tag !== 1) throw Error(w(309));
        var r = n.stateNode;
      }
      if (!r) throw Error(w(147, e));
      var l = r, i = "" + e;
      return t !== null && t.ref !== null && typeof t.ref == "function" && t.ref._stringRef === i ? t.ref : (t = function(o) {
        var u = l.refs;
        o === null ? delete u[i] : u[i] = o;
      }, t._stringRef = i, t);
    }
    if (typeof e != "string") throw Error(w(284));
    if (!n._owner) throw Error(w(290, e));
  }
  return e;
}
function Yr(e, t) {
  throw e = Object.prototype.toString.call(t), Error(w(31, e === "[object Object]" ? "object with keys {" + Object.keys(t).join(", ") + "}" : e));
}
function ls(e) {
  var t = e._init;
  return t(e._payload);
}
function Ha(e) {
  function t(f, c) {
    if (e) {
      var p = f.deletions;
      p === null ? (f.deletions = [c], f.flags |= 16) : p.push(c);
    }
  }
  function n(f, c) {
    if (!e) return null;
    for (; c !== null; ) t(f, c), c = c.sibling;
    return null;
  }
  function r(f, c) {
    for (f = /* @__PURE__ */ new Map(); c !== null; ) c.key !== null ? f.set(c.key, c) : f.set(c.index, c), c = c.sibling;
    return f;
  }
  function l(f, c) {
    return f = Mt(f, c), f.index = 0, f.sibling = null, f;
  }
  function i(f, c, p) {
    return f.index = p, e ? (p = f.alternate, p !== null ? (p = p.index, p < c ? (f.flags |= 2, c) : p) : (f.flags |= 2, c)) : (f.flags |= 1048576, c);
  }
  function o(f) {
    return e && f.alternate === null && (f.flags |= 2), f;
  }
  function u(f, c, p, y) {
    return c === null || c.tag !== 6 ? (c = Ei(p, f.mode, y), c.return = f, c) : (c = l(c, p), c.return = f, c);
  }
  function s(f, c, p, y) {
    var C = p.type;
    return C === an ? m(f, c, p.props.children, y, p.key) : c !== null && (c.elementType === C || typeof C == "object" && C !== null && C.$$typeof === St && ls(C) === c.type) ? (y = l(c, p.props), y.ref = Qn(f, c, p), y.return = f, y) : (y = fl(p.type, p.key, p.props, null, f.mode, y), y.ref = Qn(f, c, p), y.return = f, y);
  }
  function d(f, c, p, y) {
    return c === null || c.tag !== 4 || c.stateNode.containerInfo !== p.containerInfo || c.stateNode.implementation !== p.implementation ? (c = Ni(p, f.mode, y), c.return = f, c) : (c = l(c, p.children || []), c.return = f, c);
  }
  function m(f, c, p, y, C) {
    return c === null || c.tag !== 7 ? (c = Zt(p, f.mode, y, C), c.return = f, c) : (c = l(c, p), c.return = f, c);
  }
  function g(f, c, p) {
    if (typeof c == "string" && c !== "" || typeof c == "number") return c = Ei("" + c, f.mode, p), c.return = f, c;
    if (typeof c == "object" && c !== null) {
      switch (c.$$typeof) {
        case Rr:
          return p = fl(c.type, c.key, c.props, null, f.mode, p), p.ref = Qn(f, null, c), p.return = f, p;
        case sn:
          return c = Ni(c, f.mode, p), c.return = f, c;
        case St:
          var y = c._init;
          return g(f, y(c._payload), p);
      }
      if (Jn(c) || $n(c)) return c = Zt(c, f.mode, p, null), c.return = f, c;
      Yr(f, c);
    }
    return null;
  }
  function h(f, c, p, y) {
    var C = c !== null ? c.key : null;
    if (typeof p == "string" && p !== "" || typeof p == "number") return C !== null ? null : u(f, c, "" + p, y);
    if (typeof p == "object" && p !== null) {
      switch (p.$$typeof) {
        case Rr:
          return p.key === C ? s(f, c, p, y) : null;
        case sn:
          return p.key === C ? d(f, c, p, y) : null;
        case St:
          return C = p._init, h(
            f,
            c,
            C(p._payload),
            y
          );
      }
      if (Jn(p) || $n(p)) return C !== null ? null : m(f, c, p, y, null);
      Yr(f, p);
    }
    return null;
  }
  function S(f, c, p, y, C) {
    if (typeof y == "string" && y !== "" || typeof y == "number") return f = f.get(p) || null, u(c, f, "" + y, C);
    if (typeof y == "object" && y !== null) {
      switch (y.$$typeof) {
        case Rr:
          return f = f.get(y.key === null ? p : y.key) || null, s(c, f, y, C);
        case sn:
          return f = f.get(y.key === null ? p : y.key) || null, d(c, f, y, C);
        case St:
          var N = y._init;
          return S(f, c, p, N(y._payload), C);
      }
      if (Jn(y) || $n(y)) return f = f.get(p) || null, m(c, f, y, C, null);
      Yr(c, y);
    }
    return null;
  }
  function z(f, c, p, y) {
    for (var C = null, N = null, P = c, T = c = 0, K = null; P !== null && T < p.length; T++) {
      P.index > T ? (K = P, P = null) : K = P.sibling;
      var O = h(f, P, p[T], y);
      if (O === null) {
        P === null && (P = K);
        break;
      }
      e && P && O.alternate === null && t(f, P), c = i(O, c, T), N === null ? C = O : N.sibling = O, N = O, P = K;
    }
    if (T === p.length) return n(f, P), Z && Kt(f, T), C;
    if (P === null) {
      for (; T < p.length; T++) P = g(f, p[T], y), P !== null && (c = i(P, c, T), N === null ? C = P : N.sibling = P, N = P);
      return Z && Kt(f, T), C;
    }
    for (P = r(f, P); T < p.length; T++) K = S(P, f, T, p[T], y), K !== null && (e && K.alternate !== null && P.delete(K.key === null ? T : K.key), c = i(K, c, T), N === null ? C = K : N.sibling = K, N = K);
    return e && P.forEach(function(ue) {
      return t(f, ue);
    }), Z && Kt(f, T), C;
  }
  function _(f, c, p, y) {
    var C = $n(p);
    if (typeof C != "function") throw Error(w(150));
    if (p = C.call(p), p == null) throw Error(w(151));
    for (var N = C = null, P = c, T = c = 0, K = null, O = p.next(); P !== null && !O.done; T++, O = p.next()) {
      P.index > T ? (K = P, P = null) : K = P.sibling;
      var ue = h(f, P, O.value, y);
      if (ue === null) {
        P === null && (P = K);
        break;
      }
      e && P && ue.alternate === null && t(f, P), c = i(ue, c, T), N === null ? C = ue : N.sibling = ue, N = ue, P = K;
    }
    if (O.done) return n(
      f,
      P
    ), Z && Kt(f, T), C;
    if (P === null) {
      for (; !O.done; T++, O = p.next()) O = g(f, O.value, y), O !== null && (c = i(O, c, T), N === null ? C = O : N.sibling = O, N = O);
      return Z && Kt(f, T), C;
    }
    for (P = r(f, P); !O.done; T++, O = p.next()) O = S(P, f, T, O.value, y), O !== null && (e && O.alternate !== null && P.delete(O.key === null ? T : O.key), c = i(O, c, T), N === null ? C = O : N.sibling = O, N = O);
    return e && P.forEach(function(Ut) {
      return t(f, Ut);
    }), Z && Kt(f, T), C;
  }
  function B(f, c, p, y) {
    if (typeof p == "object" && p !== null && p.type === an && p.key === null && (p = p.props.children), typeof p == "object" && p !== null) {
      switch (p.$$typeof) {
        case Rr:
          e: {
            for (var C = p.key, N = c; N !== null; ) {
              if (N.key === C) {
                if (C = p.type, C === an) {
                  if (N.tag === 7) {
                    n(f, N.sibling), c = l(N, p.props.children), c.return = f, f = c;
                    break e;
                  }
                } else if (N.elementType === C || typeof C == "object" && C !== null && C.$$typeof === St && ls(C) === N.type) {
                  n(f, N.sibling), c = l(N, p.props), c.ref = Qn(f, N, p), c.return = f, f = c;
                  break e;
                }
                n(f, N);
                break;
              } else t(f, N);
              N = N.sibling;
            }
            p.type === an ? (c = Zt(p.props.children, f.mode, y, p.key), c.return = f, f = c) : (y = fl(p.type, p.key, p.props, null, f.mode, y), y.ref = Qn(f, c, p), y.return = f, f = y);
          }
          return o(f);
        case sn:
          e: {
            for (N = p.key; c !== null; ) {
              if (c.key === N) if (c.tag === 4 && c.stateNode.containerInfo === p.containerInfo && c.stateNode.implementation === p.implementation) {
                n(f, c.sibling), c = l(c, p.children || []), c.return = f, f = c;
                break e;
              } else {
                n(f, c);
                break;
              }
              else t(f, c);
              c = c.sibling;
            }
            c = Ni(p, f.mode, y), c.return = f, f = c;
          }
          return o(f);
        case St:
          return N = p._init, B(f, c, N(p._payload), y);
      }
      if (Jn(p)) return z(f, c, p, y);
      if ($n(p)) return _(f, c, p, y);
      Yr(f, p);
    }
    return typeof p == "string" && p !== "" || typeof p == "number" ? (p = "" + p, c !== null && c.tag === 6 ? (n(f, c.sibling), c = l(c, p), c.return = f, f = c) : (n(f, c), c = Ei(p, f.mode, y), c.return = f, f = c), o(f)) : n(f, c);
  }
  return B;
}
var Pn = Ha(!0), Qa = Ha(!1), Cl = Dt(null), jl = null, vn = null, Qo = null;
function Go() {
  Qo = vn = jl = null;
}
function Yo(e) {
  var t = Cl.current;
  X(Cl), e._currentValue = t;
}
function lo(e, t, n) {
  for (; e !== null; ) {
    var r = e.alternate;
    if ((e.childLanes & t) !== t ? (e.childLanes |= t, r !== null && (r.childLanes |= t)) : r !== null && (r.childLanes & t) !== t && (r.childLanes |= t), e === n) break;
    e = e.return;
  }
}
function zn(e, t) {
  jl = e, Qo = vn = null, e = e.dependencies, e !== null && e.firstContext !== null && (e.lanes & t && (ze = !0), e.firstContext = null);
}
function Ve(e) {
  var t = e._currentValue;
  if (Qo !== e) if (e = { context: e, memoizedValue: t, next: null }, vn === null) {
    if (jl === null) throw Error(w(308));
    vn = e, jl.dependencies = { lanes: 0, firstContext: e };
  } else vn = vn.next = e;
  return t;
}
var Gt = null;
function Xo(e) {
  Gt === null ? Gt = [e] : Gt.push(e);
}
function Ga(e, t, n, r) {
  var l = t.interleaved;
  return l === null ? (n.next = n, Xo(t)) : (n.next = l.next, l.next = n), t.interleaved = n, mt(e, r);
}
function mt(e, t) {
  e.lanes |= t;
  var n = e.alternate;
  for (n !== null && (n.lanes |= t), n = e, e = e.return; e !== null; ) e.childLanes |= t, n = e.alternate, n !== null && (n.childLanes |= t), n = e, e = e.return;
  return n.tag === 3 ? n.stateNode : null;
}
var _t = !1;
function Zo(e) {
  e.updateQueue = { baseState: e.memoizedState, firstBaseUpdate: null, lastBaseUpdate: null, shared: { pending: null, interleaved: null, lanes: 0 }, effects: null };
}
function Ya(e, t) {
  e = e.updateQueue, t.updateQueue === e && (t.updateQueue = { baseState: e.baseState, firstBaseUpdate: e.firstBaseUpdate, lastBaseUpdate: e.lastBaseUpdate, shared: e.shared, effects: e.effects });
}
function dt(e, t) {
  return { eventTime: e, lane: t, tag: 0, payload: null, callback: null, next: null };
}
function Lt(e, t, n) {
  var r = e.updateQueue;
  if (r === null) return null;
  if (r = r.shared, $ & 2) {
    var l = r.pending;
    return l === null ? t.next = t : (t.next = l.next, l.next = t), r.pending = t, mt(e, n);
  }
  return l = r.interleaved, l === null ? (t.next = t, Xo(r)) : (t.next = l.next, l.next = t), r.interleaved = t, mt(e, n);
}
function ol(e, t, n) {
  if (t = t.updateQueue, t !== null && (t = t.shared, (n & 4194240) !== 0)) {
    var r = t.lanes;
    r &= e.pendingLanes, n |= r, t.lanes = n, Oo(e, n);
  }
}
function is(e, t) {
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
function Pl(e, t, n, r) {
  var l = e.updateQueue;
  _t = !1;
  var i = l.firstBaseUpdate, o = l.lastBaseUpdate, u = l.shared.pending;
  if (u !== null) {
    l.shared.pending = null;
    var s = u, d = s.next;
    s.next = null, o === null ? i = d : o.next = d, o = s;
    var m = e.alternate;
    m !== null && (m = m.updateQueue, u = m.lastBaseUpdate, u !== o && (u === null ? m.firstBaseUpdate = d : u.next = d, m.lastBaseUpdate = s));
  }
  if (i !== null) {
    var g = l.baseState;
    o = 0, m = d = s = null, u = i;
    do {
      var h = u.lane, S = u.eventTime;
      if ((r & h) === h) {
        m !== null && (m = m.next = {
          eventTime: S,
          lane: 0,
          tag: u.tag,
          payload: u.payload,
          callback: u.callback,
          next: null
        });
        e: {
          var z = e, _ = u;
          switch (h = t, S = n, _.tag) {
            case 1:
              if (z = _.payload, typeof z == "function") {
                g = z.call(S, g, h);
                break e;
              }
              g = z;
              break e;
            case 3:
              z.flags = z.flags & -65537 | 128;
            case 0:
              if (z = _.payload, h = typeof z == "function" ? z.call(S, g, h) : z, h == null) break e;
              g = b({}, g, h);
              break e;
            case 2:
              _t = !0;
          }
        }
        u.callback !== null && u.lane !== 0 && (e.flags |= 64, h = l.effects, h === null ? l.effects = [u] : h.push(u));
      } else S = { eventTime: S, lane: h, tag: u.tag, payload: u.payload, callback: u.callback, next: null }, m === null ? (d = m = S, s = g) : m = m.next = S, o |= h;
      if (u = u.next, u === null) {
        if (u = l.shared.pending, u === null) break;
        h = u, u = h.next, h.next = null, l.lastBaseUpdate = h, l.shared.pending = null;
      }
    } while (!0);
    if (m === null && (s = g), l.baseState = s, l.firstBaseUpdate = d, l.lastBaseUpdate = m, t = l.shared.interleaved, t !== null) {
      l = t;
      do
        o |= l.lane, l = l.next;
      while (l !== t);
    } else i === null && (l.shared.lanes = 0);
    en |= o, e.lanes = o, e.memoizedState = g;
  }
}
function os(e, t, n) {
  if (e = t.effects, t.effects = null, e !== null) for (t = 0; t < e.length; t++) {
    var r = e[t], l = r.callback;
    if (l !== null) {
      if (r.callback = null, r = n, typeof l != "function") throw Error(w(191, l));
      l.call(r);
    }
  }
}
var Lr = {}, lt = Dt(Lr), Sr = Dt(Lr), _r = Dt(Lr);
function Yt(e) {
  if (e === Lr) throw Error(w(174));
  return e;
}
function Jo(e, t) {
  switch (G(_r, t), G(Sr, e), G(lt, Lr), e = t.nodeType, e) {
    case 9:
    case 11:
      t = (t = t.documentElement) ? t.namespaceURI : Di(null, "");
      break;
    default:
      e = e === 8 ? t.parentNode : t, t = e.namespaceURI || null, e = e.tagName, t = Di(t, e);
  }
  X(lt), G(lt, t);
}
function Tn() {
  X(lt), X(Sr), X(_r);
}
function Xa(e) {
  Yt(_r.current);
  var t = Yt(lt.current), n = Di(t, e.type);
  t !== n && (G(Sr, e), G(lt, n));
}
function qo(e) {
  Sr.current === e && (X(lt), X(Sr));
}
var J = Dt(0);
function Tl(e) {
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
var wi = [];
function bo() {
  for (var e = 0; e < wi.length; e++) wi[e]._workInProgressVersionPrimary = null;
  wi.length = 0;
}
var ul = gt.ReactCurrentDispatcher, ki = gt.ReactCurrentBatchConfig, bt = 0, q = null, ie = null, ae = null, Ll = !1, or = !1, xr = 0, bf = 0;
function me() {
  throw Error(w(321));
}
function eu(e, t) {
  if (t === null) return !1;
  for (var n = 0; n < t.length && n < e.length; n++) if (!qe(e[n], t[n])) return !1;
  return !0;
}
function tu(e, t, n, r, l, i) {
  if (bt = i, q = t, t.memoizedState = null, t.updateQueue = null, t.lanes = 0, ul.current = e === null || e.memoizedState === null ? rp : lp, e = n(r, l), or) {
    i = 0;
    do {
      if (or = !1, xr = 0, 25 <= i) throw Error(w(301));
      i += 1, ae = ie = null, t.updateQueue = null, ul.current = ip, e = n(r, l);
    } while (or);
  }
  if (ul.current = Wl, t = ie !== null && ie.next !== null, bt = 0, ae = ie = q = null, Ll = !1, t) throw Error(w(300));
  return e;
}
function nu() {
  var e = xr !== 0;
  return xr = 0, e;
}
function tt() {
  var e = { memoizedState: null, baseState: null, baseQueue: null, queue: null, next: null };
  return ae === null ? q.memoizedState = ae = e : ae = ae.next = e, ae;
}
function Ke() {
  if (ie === null) {
    var e = q.alternate;
    e = e !== null ? e.memoizedState : null;
  } else e = ie.next;
  var t = ae === null ? q.memoizedState : ae.next;
  if (t !== null) ae = t, ie = e;
  else {
    if (e === null) throw Error(w(310));
    ie = e, e = { memoizedState: ie.memoizedState, baseState: ie.baseState, baseQueue: ie.baseQueue, queue: ie.queue, next: null }, ae === null ? q.memoizedState = ae = e : ae = ae.next = e;
  }
  return ae;
}
function zr(e, t) {
  return typeof t == "function" ? t(e) : t;
}
function Si(e) {
  var t = Ke(), n = t.queue;
  if (n === null) throw Error(w(311));
  n.lastRenderedReducer = e;
  var r = ie, l = r.baseQueue, i = n.pending;
  if (i !== null) {
    if (l !== null) {
      var o = l.next;
      l.next = i.next, i.next = o;
    }
    r.baseQueue = l = i, n.pending = null;
  }
  if (l !== null) {
    i = l.next, r = r.baseState;
    var u = o = null, s = null, d = i;
    do {
      var m = d.lane;
      if ((bt & m) === m) s !== null && (s = s.next = { lane: 0, action: d.action, hasEagerState: d.hasEagerState, eagerState: d.eagerState, next: null }), r = d.hasEagerState ? d.eagerState : e(r, d.action);
      else {
        var g = {
          lane: m,
          action: d.action,
          hasEagerState: d.hasEagerState,
          eagerState: d.eagerState,
          next: null
        };
        s === null ? (u = s = g, o = r) : s = s.next = g, q.lanes |= m, en |= m;
      }
      d = d.next;
    } while (d !== null && d !== i);
    s === null ? o = r : s.next = u, qe(r, t.memoizedState) || (ze = !0), t.memoizedState = r, t.baseState = o, t.baseQueue = s, n.lastRenderedState = r;
  }
  if (e = n.interleaved, e !== null) {
    l = e;
    do
      i = l.lane, q.lanes |= i, en |= i, l = l.next;
    while (l !== e);
  } else l === null && (n.lanes = 0);
  return [t.memoizedState, n.dispatch];
}
function _i(e) {
  var t = Ke(), n = t.queue;
  if (n === null) throw Error(w(311));
  n.lastRenderedReducer = e;
  var r = n.dispatch, l = n.pending, i = t.memoizedState;
  if (l !== null) {
    n.pending = null;
    var o = l = l.next;
    do
      i = e(i, o.action), o = o.next;
    while (o !== l);
    qe(i, t.memoizedState) || (ze = !0), t.memoizedState = i, t.baseQueue === null && (t.baseState = i), n.lastRenderedState = i;
  }
  return [i, r];
}
function Za() {
}
function Ja(e, t) {
  var n = q, r = Ke(), l = t(), i = !qe(r.memoizedState, l);
  if (i && (r.memoizedState = l, ze = !0), r = r.queue, ru(ec.bind(null, n, r, e), [e]), r.getSnapshot !== t || i || ae !== null && ae.memoizedState.tag & 1) {
    if (n.flags |= 2048, Er(9, ba.bind(null, n, r, l, t), void 0, null), ce === null) throw Error(w(349));
    bt & 30 || qa(n, t, l);
  }
  return l;
}
function qa(e, t, n) {
  e.flags |= 16384, e = { getSnapshot: t, value: n }, t = q.updateQueue, t === null ? (t = { lastEffect: null, stores: null }, q.updateQueue = t, t.stores = [e]) : (n = t.stores, n === null ? t.stores = [e] : n.push(e));
}
function ba(e, t, n, r) {
  t.value = n, t.getSnapshot = r, tc(t) && nc(e);
}
function ec(e, t, n) {
  return n(function() {
    tc(t) && nc(e);
  });
}
function tc(e) {
  var t = e.getSnapshot;
  e = e.value;
  try {
    var n = t();
    return !qe(e, n);
  } catch {
    return !0;
  }
}
function nc(e) {
  var t = mt(e, 1);
  t !== null && Je(t, e, 1, -1);
}
function us(e) {
  var t = tt();
  return typeof e == "function" && (e = e()), t.memoizedState = t.baseState = e, e = { pending: null, interleaved: null, lanes: 0, dispatch: null, lastRenderedReducer: zr, lastRenderedState: e }, t.queue = e, e = e.dispatch = np.bind(null, q, e), [t.memoizedState, e];
}
function Er(e, t, n, r) {
  return e = { tag: e, create: t, destroy: n, deps: r, next: null }, t = q.updateQueue, t === null ? (t = { lastEffect: null, stores: null }, q.updateQueue = t, t.lastEffect = e.next = e) : (n = t.lastEffect, n === null ? t.lastEffect = e.next = e : (r = n.next, n.next = e, e.next = r, t.lastEffect = e)), e;
}
function rc() {
  return Ke().memoizedState;
}
function sl(e, t, n, r) {
  var l = tt();
  q.flags |= e, l.memoizedState = Er(1 | t, n, void 0, r === void 0 ? null : r);
}
function Hl(e, t, n, r) {
  var l = Ke();
  r = r === void 0 ? null : r;
  var i = void 0;
  if (ie !== null) {
    var o = ie.memoizedState;
    if (i = o.destroy, r !== null && eu(r, o.deps)) {
      l.memoizedState = Er(t, n, i, r);
      return;
    }
  }
  q.flags |= e, l.memoizedState = Er(1 | t, n, i, r);
}
function ss(e, t) {
  return sl(8390656, 8, e, t);
}
function ru(e, t) {
  return Hl(2048, 8, e, t);
}
function lc(e, t) {
  return Hl(4, 2, e, t);
}
function ic(e, t) {
  return Hl(4, 4, e, t);
}
function oc(e, t) {
  if (typeof t == "function") return e = e(), t(e), function() {
    t(null);
  };
  if (t != null) return e = e(), t.current = e, function() {
    t.current = null;
  };
}
function uc(e, t, n) {
  return n = n != null ? n.concat([e]) : null, Hl(4, 4, oc.bind(null, t, e), n);
}
function lu() {
}
function sc(e, t) {
  var n = Ke();
  t = t === void 0 ? null : t;
  var r = n.memoizedState;
  return r !== null && t !== null && eu(t, r[1]) ? r[0] : (n.memoizedState = [e, t], e);
}
function ac(e, t) {
  var n = Ke();
  t = t === void 0 ? null : t;
  var r = n.memoizedState;
  return r !== null && t !== null && eu(t, r[1]) ? r[0] : (e = e(), n.memoizedState = [e, t], e);
}
function cc(e, t, n) {
  return bt & 21 ? (qe(n, t) || (n = ha(), q.lanes |= n, en |= n, e.baseState = !0), t) : (e.baseState && (e.baseState = !1, ze = !0), e.memoizedState = n);
}
function ep(e, t) {
  var n = V;
  V = n !== 0 && 4 > n ? n : 4, e(!0);
  var r = ki.transition;
  ki.transition = {};
  try {
    e(!1), t();
  } finally {
    V = n, ki.transition = r;
  }
}
function dc() {
  return Ke().memoizedState;
}
function tp(e, t, n) {
  var r = Ft(e);
  if (n = { lane: r, action: n, hasEagerState: !1, eagerState: null, next: null }, fc(e)) pc(t, n);
  else if (n = Ga(e, t, n, r), n !== null) {
    var l = we();
    Je(n, e, r, l), mc(n, t, r);
  }
}
function np(e, t, n) {
  var r = Ft(e), l = { lane: r, action: n, hasEagerState: !1, eagerState: null, next: null };
  if (fc(e)) pc(t, l);
  else {
    var i = e.alternate;
    if (e.lanes === 0 && (i === null || i.lanes === 0) && (i = t.lastRenderedReducer, i !== null)) try {
      var o = t.lastRenderedState, u = i(o, n);
      if (l.hasEagerState = !0, l.eagerState = u, qe(u, o)) {
        var s = t.interleaved;
        s === null ? (l.next = l, Xo(t)) : (l.next = s.next, s.next = l), t.interleaved = l;
        return;
      }
    } catch {
    } finally {
    }
    n = Ga(e, t, l, r), n !== null && (l = we(), Je(n, e, r, l), mc(n, t, r));
  }
}
function fc(e) {
  var t = e.alternate;
  return e === q || t !== null && t === q;
}
function pc(e, t) {
  or = Ll = !0;
  var n = e.pending;
  n === null ? t.next = t : (t.next = n.next, n.next = t), e.pending = t;
}
function mc(e, t, n) {
  if (n & 4194240) {
    var r = t.lanes;
    r &= e.pendingLanes, n |= r, t.lanes = n, Oo(e, n);
  }
}
var Wl = { readContext: Ve, useCallback: me, useContext: me, useEffect: me, useImperativeHandle: me, useInsertionEffect: me, useLayoutEffect: me, useMemo: me, useReducer: me, useRef: me, useState: me, useDebugValue: me, useDeferredValue: me, useTransition: me, useMutableSource: me, useSyncExternalStore: me, useId: me, unstable_isNewReconciler: !1 }, rp = { readContext: Ve, useCallback: function(e, t) {
  return tt().memoizedState = [e, t === void 0 ? null : t], e;
}, useContext: Ve, useEffect: ss, useImperativeHandle: function(e, t, n) {
  return n = n != null ? n.concat([e]) : null, sl(
    4194308,
    4,
    oc.bind(null, t, e),
    n
  );
}, useLayoutEffect: function(e, t) {
  return sl(4194308, 4, e, t);
}, useInsertionEffect: function(e, t) {
  return sl(4, 2, e, t);
}, useMemo: function(e, t) {
  var n = tt();
  return t = t === void 0 ? null : t, e = e(), n.memoizedState = [e, t], e;
}, useReducer: function(e, t, n) {
  var r = tt();
  return t = n !== void 0 ? n(t) : t, r.memoizedState = r.baseState = t, e = { pending: null, interleaved: null, lanes: 0, dispatch: null, lastRenderedReducer: e, lastRenderedState: t }, r.queue = e, e = e.dispatch = tp.bind(null, q, e), [r.memoizedState, e];
}, useRef: function(e) {
  var t = tt();
  return e = { current: e }, t.memoizedState = e;
}, useState: us, useDebugValue: lu, useDeferredValue: function(e) {
  return tt().memoizedState = e;
}, useTransition: function() {
  var e = us(!1), t = e[0];
  return e = ep.bind(null, e[1]), tt().memoizedState = e, [t, e];
}, useMutableSource: function() {
}, useSyncExternalStore: function(e, t, n) {
  var r = q, l = tt();
  if (Z) {
    if (n === void 0) throw Error(w(407));
    n = n();
  } else {
    if (n = t(), ce === null) throw Error(w(349));
    bt & 30 || qa(r, t, n);
  }
  l.memoizedState = n;
  var i = { value: n, getSnapshot: t };
  return l.queue = i, ss(ec.bind(
    null,
    r,
    i,
    e
  ), [e]), r.flags |= 2048, Er(9, ba.bind(null, r, i, n, t), void 0, null), n;
}, useId: function() {
  var e = tt(), t = ce.identifierPrefix;
  if (Z) {
    var n = ct, r = at;
    n = (r & ~(1 << 32 - Ze(r) - 1)).toString(32) + n, t = ":" + t + "R" + n, n = xr++, 0 < n && (t += "H" + n.toString(32)), t += ":";
  } else n = bf++, t = ":" + t + "r" + n.toString(32) + ":";
  return e.memoizedState = t;
}, unstable_isNewReconciler: !1 }, lp = {
  readContext: Ve,
  useCallback: sc,
  useContext: Ve,
  useEffect: ru,
  useImperativeHandle: uc,
  useInsertionEffect: lc,
  useLayoutEffect: ic,
  useMemo: ac,
  useReducer: Si,
  useRef: rc,
  useState: function() {
    return Si(zr);
  },
  useDebugValue: lu,
  useDeferredValue: function(e) {
    var t = Ke();
    return cc(t, ie.memoizedState, e);
  },
  useTransition: function() {
    var e = Si(zr)[0], t = Ke().memoizedState;
    return [e, t];
  },
  useMutableSource: Za,
  useSyncExternalStore: Ja,
  useId: dc,
  unstable_isNewReconciler: !1
}, ip = { readContext: Ve, useCallback: sc, useContext: Ve, useEffect: ru, useImperativeHandle: uc, useInsertionEffect: lc, useLayoutEffect: ic, useMemo: ac, useReducer: _i, useRef: rc, useState: function() {
  return _i(zr);
}, useDebugValue: lu, useDeferredValue: function(e) {
  var t = Ke();
  return ie === null ? t.memoizedState = e : cc(t, ie.memoizedState, e);
}, useTransition: function() {
  var e = _i(zr)[0], t = Ke().memoizedState;
  return [e, t];
}, useMutableSource: Za, useSyncExternalStore: Ja, useId: dc, unstable_isNewReconciler: !1 };
function Ge(e, t) {
  if (e && e.defaultProps) {
    t = b({}, t), e = e.defaultProps;
    for (var n in e) t[n] === void 0 && (t[n] = e[n]);
    return t;
  }
  return t;
}
function io(e, t, n, r) {
  t = e.memoizedState, n = n(r, t), n = n == null ? t : b({}, t, n), e.memoizedState = n, e.lanes === 0 && (e.updateQueue.baseState = n);
}
var Ql = { isMounted: function(e) {
  return (e = e._reactInternals) ? rn(e) === e : !1;
}, enqueueSetState: function(e, t, n) {
  e = e._reactInternals;
  var r = we(), l = Ft(e), i = dt(r, l);
  i.payload = t, n != null && (i.callback = n), t = Lt(e, i, l), t !== null && (Je(t, e, l, r), ol(t, e, l));
}, enqueueReplaceState: function(e, t, n) {
  e = e._reactInternals;
  var r = we(), l = Ft(e), i = dt(r, l);
  i.tag = 1, i.payload = t, n != null && (i.callback = n), t = Lt(e, i, l), t !== null && (Je(t, e, l, r), ol(t, e, l));
}, enqueueForceUpdate: function(e, t) {
  e = e._reactInternals;
  var n = we(), r = Ft(e), l = dt(n, r);
  l.tag = 2, t != null && (l.callback = t), t = Lt(e, l, r), t !== null && (Je(t, e, r, n), ol(t, e, r));
} };
function as(e, t, n, r, l, i, o) {
  return e = e.stateNode, typeof e.shouldComponentUpdate == "function" ? e.shouldComponentUpdate(r, i, o) : t.prototype && t.prototype.isPureReactComponent ? !vr(n, r) || !vr(l, i) : !0;
}
function hc(e, t, n) {
  var r = !1, l = At, i = t.contextType;
  return typeof i == "object" && i !== null ? i = Ve(i) : (l = Ne(t) ? Jt : ve.current, r = t.contextTypes, i = (r = r != null) ? Cn(e, l) : At), t = new t(n, i), e.memoizedState = t.state !== null && t.state !== void 0 ? t.state : null, t.updater = Ql, e.stateNode = t, t._reactInternals = e, r && (e = e.stateNode, e.__reactInternalMemoizedUnmaskedChildContext = l, e.__reactInternalMemoizedMaskedChildContext = i), t;
}
function cs(e, t, n, r) {
  e = t.state, typeof t.componentWillReceiveProps == "function" && t.componentWillReceiveProps(n, r), typeof t.UNSAFE_componentWillReceiveProps == "function" && t.UNSAFE_componentWillReceiveProps(n, r), t.state !== e && Ql.enqueueReplaceState(t, t.state, null);
}
function oo(e, t, n, r) {
  var l = e.stateNode;
  l.props = n, l.state = e.memoizedState, l.refs = {}, Zo(e);
  var i = t.contextType;
  typeof i == "object" && i !== null ? l.context = Ve(i) : (i = Ne(t) ? Jt : ve.current, l.context = Cn(e, i)), l.state = e.memoizedState, i = t.getDerivedStateFromProps, typeof i == "function" && (io(e, t, i, n), l.state = e.memoizedState), typeof t.getDerivedStateFromProps == "function" || typeof l.getSnapshotBeforeUpdate == "function" || typeof l.UNSAFE_componentWillMount != "function" && typeof l.componentWillMount != "function" || (t = l.state, typeof l.componentWillMount == "function" && l.componentWillMount(), typeof l.UNSAFE_componentWillMount == "function" && l.UNSAFE_componentWillMount(), t !== l.state && Ql.enqueueReplaceState(l, l.state, null), Pl(e, n, l, r), l.state = e.memoizedState), typeof l.componentDidMount == "function" && (e.flags |= 4194308);
}
function Ln(e, t) {
  try {
    var n = "", r = t;
    do
      n += Wd(r), r = r.return;
    while (r);
    var l = n;
  } catch (i) {
    l = `
Error generating stack: ` + i.message + `
` + i.stack;
  }
  return { value: e, source: t, stack: l, digest: null };
}
function xi(e, t, n) {
  return { value: e, source: null, stack: n ?? null, digest: t ?? null };
}
function uo(e, t) {
  try {
    console.error(t.value);
  } catch (n) {
    setTimeout(function() {
      throw n;
    });
  }
}
var op = typeof WeakMap == "function" ? WeakMap : Map;
function gc(e, t, n) {
  n = dt(-1, n), n.tag = 3, n.payload = { element: null };
  var r = t.value;
  return n.callback = function() {
    Ml || (Ml = !0, yo = r), uo(e, t);
  }, n;
}
function vc(e, t, n) {
  n = dt(-1, n), n.tag = 3;
  var r = e.type.getDerivedStateFromError;
  if (typeof r == "function") {
    var l = t.value;
    n.payload = function() {
      return r(l);
    }, n.callback = function() {
      uo(e, t);
    };
  }
  var i = e.stateNode;
  return i !== null && typeof i.componentDidCatch == "function" && (n.callback = function() {
    uo(e, t), typeof r != "function" && (Wt === null ? Wt = /* @__PURE__ */ new Set([this]) : Wt.add(this));
    var o = t.stack;
    this.componentDidCatch(t.value, { componentStack: o !== null ? o : "" });
  }), n;
}
function ds(e, t, n) {
  var r = e.pingCache;
  if (r === null) {
    r = e.pingCache = new op();
    var l = /* @__PURE__ */ new Set();
    r.set(t, l);
  } else l = r.get(t), l === void 0 && (l = /* @__PURE__ */ new Set(), r.set(t, l));
  l.has(n) || (l.add(n), e = kp.bind(null, e, t, n), t.then(e, e));
}
function fs(e) {
  do {
    var t;
    if ((t = e.tag === 13) && (t = e.memoizedState, t = t !== null ? t.dehydrated !== null : !0), t) return e;
    e = e.return;
  } while (e !== null);
  return null;
}
function ps(e, t, n, r, l) {
  return e.mode & 1 ? (e.flags |= 65536, e.lanes = l, e) : (e === t ? e.flags |= 65536 : (e.flags |= 128, n.flags |= 131072, n.flags &= -52805, n.tag === 1 && (n.alternate === null ? n.tag = 17 : (t = dt(-1, 1), t.tag = 2, Lt(n, t, 1))), n.lanes |= 1), e);
}
var up = gt.ReactCurrentOwner, ze = !1;
function ye(e, t, n, r) {
  t.child = e === null ? Qa(t, null, n, r) : Pn(t, e.child, n, r);
}
function ms(e, t, n, r, l) {
  n = n.render;
  var i = t.ref;
  return zn(t, l), r = tu(e, t, n, r, i, l), n = nu(), e !== null && !ze ? (t.updateQueue = e.updateQueue, t.flags &= -2053, e.lanes &= ~l, ht(e, t, l)) : (Z && n && Ko(t), t.flags |= 1, ye(e, t, r, l), t.child);
}
function hs(e, t, n, r, l) {
  if (e === null) {
    var i = n.type;
    return typeof i == "function" && !fu(i) && i.defaultProps === void 0 && n.compare === null && n.defaultProps === void 0 ? (t.tag = 15, t.type = i, yc(e, t, i, r, l)) : (e = fl(n.type, null, r, t, t.mode, l), e.ref = t.ref, e.return = t, t.child = e);
  }
  if (i = e.child, !(e.lanes & l)) {
    var o = i.memoizedProps;
    if (n = n.compare, n = n !== null ? n : vr, n(o, r) && e.ref === t.ref) return ht(e, t, l);
  }
  return t.flags |= 1, e = Mt(i, r), e.ref = t.ref, e.return = t, t.child = e;
}
function yc(e, t, n, r, l) {
  if (e !== null) {
    var i = e.memoizedProps;
    if (vr(i, r) && e.ref === t.ref) if (ze = !1, t.pendingProps = r = i, (e.lanes & l) !== 0) e.flags & 131072 && (ze = !0);
    else return t.lanes = e.lanes, ht(e, t, l);
  }
  return so(e, t, n, r, l);
}
function wc(e, t, n) {
  var r = t.pendingProps, l = r.children, i = e !== null ? e.memoizedState : null;
  if (r.mode === "hidden") if (!(t.mode & 1)) t.memoizedState = { baseLanes: 0, cachePool: null, transitions: null }, G(wn, Pe), Pe |= n;
  else {
    if (!(n & 1073741824)) return e = i !== null ? i.baseLanes | n : n, t.lanes = t.childLanes = 1073741824, t.memoizedState = { baseLanes: e, cachePool: null, transitions: null }, t.updateQueue = null, G(wn, Pe), Pe |= e, null;
    t.memoizedState = { baseLanes: 0, cachePool: null, transitions: null }, r = i !== null ? i.baseLanes : n, G(wn, Pe), Pe |= r;
  }
  else i !== null ? (r = i.baseLanes | n, t.memoizedState = null) : r = n, G(wn, Pe), Pe |= r;
  return ye(e, t, l, n), t.child;
}
function kc(e, t) {
  var n = t.ref;
  (e === null && n !== null || e !== null && e.ref !== n) && (t.flags |= 512, t.flags |= 2097152);
}
function so(e, t, n, r, l) {
  var i = Ne(n) ? Jt : ve.current;
  return i = Cn(t, i), zn(t, l), n = tu(e, t, n, r, i, l), r = nu(), e !== null && !ze ? (t.updateQueue = e.updateQueue, t.flags &= -2053, e.lanes &= ~l, ht(e, t, l)) : (Z && r && Ko(t), t.flags |= 1, ye(e, t, n, l), t.child);
}
function gs(e, t, n, r, l) {
  if (Ne(n)) {
    var i = !0;
    zl(t);
  } else i = !1;
  if (zn(t, l), t.stateNode === null) al(e, t), hc(t, n, r), oo(t, n, r, l), r = !0;
  else if (e === null) {
    var o = t.stateNode, u = t.memoizedProps;
    o.props = u;
    var s = o.context, d = n.contextType;
    typeof d == "object" && d !== null ? d = Ve(d) : (d = Ne(n) ? Jt : ve.current, d = Cn(t, d));
    var m = n.getDerivedStateFromProps, g = typeof m == "function" || typeof o.getSnapshotBeforeUpdate == "function";
    g || typeof o.UNSAFE_componentWillReceiveProps != "function" && typeof o.componentWillReceiveProps != "function" || (u !== r || s !== d) && cs(t, o, r, d), _t = !1;
    var h = t.memoizedState;
    o.state = h, Pl(t, r, o, l), s = t.memoizedState, u !== r || h !== s || Ee.current || _t ? (typeof m == "function" && (io(t, n, m, r), s = t.memoizedState), (u = _t || as(t, n, u, r, h, s, d)) ? (g || typeof o.UNSAFE_componentWillMount != "function" && typeof o.componentWillMount != "function" || (typeof o.componentWillMount == "function" && o.componentWillMount(), typeof o.UNSAFE_componentWillMount == "function" && o.UNSAFE_componentWillMount()), typeof o.componentDidMount == "function" && (t.flags |= 4194308)) : (typeof o.componentDidMount == "function" && (t.flags |= 4194308), t.memoizedProps = r, t.memoizedState = s), o.props = r, o.state = s, o.context = d, r = u) : (typeof o.componentDidMount == "function" && (t.flags |= 4194308), r = !1);
  } else {
    o = t.stateNode, Ya(e, t), u = t.memoizedProps, d = t.type === t.elementType ? u : Ge(t.type, u), o.props = d, g = t.pendingProps, h = o.context, s = n.contextType, typeof s == "object" && s !== null ? s = Ve(s) : (s = Ne(n) ? Jt : ve.current, s = Cn(t, s));
    var S = n.getDerivedStateFromProps;
    (m = typeof S == "function" || typeof o.getSnapshotBeforeUpdate == "function") || typeof o.UNSAFE_componentWillReceiveProps != "function" && typeof o.componentWillReceiveProps != "function" || (u !== g || h !== s) && cs(t, o, r, s), _t = !1, h = t.memoizedState, o.state = h, Pl(t, r, o, l);
    var z = t.memoizedState;
    u !== g || h !== z || Ee.current || _t ? (typeof S == "function" && (io(t, n, S, r), z = t.memoizedState), (d = _t || as(t, n, d, r, h, z, s) || !1) ? (m || typeof o.UNSAFE_componentWillUpdate != "function" && typeof o.componentWillUpdate != "function" || (typeof o.componentWillUpdate == "function" && o.componentWillUpdate(r, z, s), typeof o.UNSAFE_componentWillUpdate == "function" && o.UNSAFE_componentWillUpdate(r, z, s)), typeof o.componentDidUpdate == "function" && (t.flags |= 4), typeof o.getSnapshotBeforeUpdate == "function" && (t.flags |= 1024)) : (typeof o.componentDidUpdate != "function" || u === e.memoizedProps && h === e.memoizedState || (t.flags |= 4), typeof o.getSnapshotBeforeUpdate != "function" || u === e.memoizedProps && h === e.memoizedState || (t.flags |= 1024), t.memoizedProps = r, t.memoizedState = z), o.props = r, o.state = z, o.context = s, r = d) : (typeof o.componentDidUpdate != "function" || u === e.memoizedProps && h === e.memoizedState || (t.flags |= 4), typeof o.getSnapshotBeforeUpdate != "function" || u === e.memoizedProps && h === e.memoizedState || (t.flags |= 1024), r = !1);
  }
  return ao(e, t, n, r, i, l);
}
function ao(e, t, n, r, l, i) {
  kc(e, t);
  var o = (t.flags & 128) !== 0;
  if (!r && !o) return l && ts(t, n, !1), ht(e, t, i);
  r = t.stateNode, up.current = t;
  var u = o && typeof n.getDerivedStateFromError != "function" ? null : r.render();
  return t.flags |= 1, e !== null && o ? (t.child = Pn(t, e.child, null, i), t.child = Pn(t, null, u, i)) : ye(e, t, u, i), t.memoizedState = r.state, l && ts(t, n, !0), t.child;
}
function Sc(e) {
  var t = e.stateNode;
  t.pendingContext ? es(e, t.pendingContext, t.pendingContext !== t.context) : t.context && es(e, t.context, !1), Jo(e, t.containerInfo);
}
function vs(e, t, n, r, l) {
  return jn(), Ho(l), t.flags |= 256, ye(e, t, n, r), t.child;
}
var co = { dehydrated: null, treeContext: null, retryLane: 0 };
function fo(e) {
  return { baseLanes: e, cachePool: null, transitions: null };
}
function _c(e, t, n) {
  var r = t.pendingProps, l = J.current, i = !1, o = (t.flags & 128) !== 0, u;
  if ((u = o) || (u = e !== null && e.memoizedState === null ? !1 : (l & 2) !== 0), u ? (i = !0, t.flags &= -129) : (e === null || e.memoizedState !== null) && (l |= 1), G(J, l & 1), e === null)
    return ro(t), e = t.memoizedState, e !== null && (e = e.dehydrated, e !== null) ? (t.mode & 1 ? e.data === "$!" ? t.lanes = 8 : t.lanes = 1073741824 : t.lanes = 1, null) : (o = r.children, e = r.fallback, i ? (r = t.mode, i = t.child, o = { mode: "hidden", children: o }, !(r & 1) && i !== null ? (i.childLanes = 0, i.pendingProps = o) : i = Xl(o, r, 0, null), e = Zt(e, r, n, null), i.return = t, e.return = t, i.sibling = e, t.child = i, t.child.memoizedState = fo(n), t.memoizedState = co, e) : iu(t, o));
  if (l = e.memoizedState, l !== null && (u = l.dehydrated, u !== null)) return sp(e, t, o, r, u, l, n);
  if (i) {
    i = r.fallback, o = t.mode, l = e.child, u = l.sibling;
    var s = { mode: "hidden", children: r.children };
    return !(o & 1) && t.child !== l ? (r = t.child, r.childLanes = 0, r.pendingProps = s, t.deletions = null) : (r = Mt(l, s), r.subtreeFlags = l.subtreeFlags & 14680064), u !== null ? i = Mt(u, i) : (i = Zt(i, o, n, null), i.flags |= 2), i.return = t, r.return = t, r.sibling = i, t.child = r, r = i, i = t.child, o = e.child.memoizedState, o = o === null ? fo(n) : { baseLanes: o.baseLanes | n, cachePool: null, transitions: o.transitions }, i.memoizedState = o, i.childLanes = e.childLanes & ~n, t.memoizedState = co, r;
  }
  return i = e.child, e = i.sibling, r = Mt(i, { mode: "visible", children: r.children }), !(t.mode & 1) && (r.lanes = n), r.return = t, r.sibling = null, e !== null && (n = t.deletions, n === null ? (t.deletions = [e], t.flags |= 16) : n.push(e)), t.child = r, t.memoizedState = null, r;
}
function iu(e, t) {
  return t = Xl({ mode: "visible", children: t }, e.mode, 0, null), t.return = e, e.child = t;
}
function Xr(e, t, n, r) {
  return r !== null && Ho(r), Pn(t, e.child, null, n), e = iu(t, t.pendingProps.children), e.flags |= 2, t.memoizedState = null, e;
}
function sp(e, t, n, r, l, i, o) {
  if (n)
    return t.flags & 256 ? (t.flags &= -257, r = xi(Error(w(422))), Xr(e, t, o, r)) : t.memoizedState !== null ? (t.child = e.child, t.flags |= 128, null) : (i = r.fallback, l = t.mode, r = Xl({ mode: "visible", children: r.children }, l, 0, null), i = Zt(i, l, o, null), i.flags |= 2, r.return = t, i.return = t, r.sibling = i, t.child = r, t.mode & 1 && Pn(t, e.child, null, o), t.child.memoizedState = fo(o), t.memoizedState = co, i);
  if (!(t.mode & 1)) return Xr(e, t, o, null);
  if (l.data === "$!") {
    if (r = l.nextSibling && l.nextSibling.dataset, r) var u = r.dgst;
    return r = u, i = Error(w(419)), r = xi(i, r, void 0), Xr(e, t, o, r);
  }
  if (u = (o & e.childLanes) !== 0, ze || u) {
    if (r = ce, r !== null) {
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
      l = l & (r.suspendedLanes | o) ? 0 : l, l !== 0 && l !== i.retryLane && (i.retryLane = l, mt(e, l), Je(r, e, l, -1));
    }
    return du(), r = xi(Error(w(421))), Xr(e, t, o, r);
  }
  return l.data === "$?" ? (t.flags |= 128, t.child = e.child, t = Sp.bind(null, e), l._reactRetry = t, null) : (e = i.treeContext, Te = Tt(l.nextSibling), We = t, Z = !0, Xe = null, e !== null && (De[Ie++] = at, De[Ie++] = ct, De[Ie++] = qt, at = e.id, ct = e.overflow, qt = t), t = iu(t, r.children), t.flags |= 4096, t);
}
function ys(e, t, n) {
  e.lanes |= t;
  var r = e.alternate;
  r !== null && (r.lanes |= t), lo(e.return, t, n);
}
function zi(e, t, n, r, l) {
  var i = e.memoizedState;
  i === null ? e.memoizedState = { isBackwards: t, rendering: null, renderingStartTime: 0, last: r, tail: n, tailMode: l } : (i.isBackwards = t, i.rendering = null, i.renderingStartTime = 0, i.last = r, i.tail = n, i.tailMode = l);
}
function xc(e, t, n) {
  var r = t.pendingProps, l = r.revealOrder, i = r.tail;
  if (ye(e, t, r.children, n), r = J.current, r & 2) r = r & 1 | 2, t.flags |= 128;
  else {
    if (e !== null && e.flags & 128) e: for (e = t.child; e !== null; ) {
      if (e.tag === 13) e.memoizedState !== null && ys(e, n, t);
      else if (e.tag === 19) ys(e, n, t);
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
  if (G(J, r), !(t.mode & 1)) t.memoizedState = null;
  else switch (l) {
    case "forwards":
      for (n = t.child, l = null; n !== null; ) e = n.alternate, e !== null && Tl(e) === null && (l = n), n = n.sibling;
      n = l, n === null ? (l = t.child, t.child = null) : (l = n.sibling, n.sibling = null), zi(t, !1, l, n, i);
      break;
    case "backwards":
      for (n = null, l = t.child, t.child = null; l !== null; ) {
        if (e = l.alternate, e !== null && Tl(e) === null) {
          t.child = l;
          break;
        }
        e = l.sibling, l.sibling = n, n = l, l = e;
      }
      zi(t, !0, n, null, i);
      break;
    case "together":
      zi(t, !1, null, null, void 0);
      break;
    default:
      t.memoizedState = null;
  }
  return t.child;
}
function al(e, t) {
  !(t.mode & 1) && e !== null && (e.alternate = null, t.alternate = null, t.flags |= 2);
}
function ht(e, t, n) {
  if (e !== null && (t.dependencies = e.dependencies), en |= t.lanes, !(n & t.childLanes)) return null;
  if (e !== null && t.child !== e.child) throw Error(w(153));
  if (t.child !== null) {
    for (e = t.child, n = Mt(e, e.pendingProps), t.child = n, n.return = t; e.sibling !== null; ) e = e.sibling, n = n.sibling = Mt(e, e.pendingProps), n.return = t;
    n.sibling = null;
  }
  return t.child;
}
function ap(e, t, n) {
  switch (t.tag) {
    case 3:
      Sc(t), jn();
      break;
    case 5:
      Xa(t);
      break;
    case 1:
      Ne(t.type) && zl(t);
      break;
    case 4:
      Jo(t, t.stateNode.containerInfo);
      break;
    case 10:
      var r = t.type._context, l = t.memoizedProps.value;
      G(Cl, r._currentValue), r._currentValue = l;
      break;
    case 13:
      if (r = t.memoizedState, r !== null)
        return r.dehydrated !== null ? (G(J, J.current & 1), t.flags |= 128, null) : n & t.child.childLanes ? _c(e, t, n) : (G(J, J.current & 1), e = ht(e, t, n), e !== null ? e.sibling : null);
      G(J, J.current & 1);
      break;
    case 19:
      if (r = (n & t.childLanes) !== 0, e.flags & 128) {
        if (r) return xc(e, t, n);
        t.flags |= 128;
      }
      if (l = t.memoizedState, l !== null && (l.rendering = null, l.tail = null, l.lastEffect = null), G(J, J.current), r) break;
      return null;
    case 22:
    case 23:
      return t.lanes = 0, wc(e, t, n);
  }
  return ht(e, t, n);
}
var zc, po, Ec, Nc;
zc = function(e, t) {
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
po = function() {
};
Ec = function(e, t, n, r) {
  var l = e.memoizedProps;
  if (l !== r) {
    e = t.stateNode, Yt(lt.current);
    var i = null;
    switch (n) {
      case "input":
        l = Mi(e, l), r = Mi(e, r), i = [];
        break;
      case "select":
        l = b({}, l, { value: void 0 }), r = b({}, r, { value: void 0 }), i = [];
        break;
      case "textarea":
        l = Ri(e, l), r = Ri(e, r), i = [];
        break;
      default:
        typeof l.onClick != "function" && typeof r.onClick == "function" && (e.onclick = _l);
    }
    Ii(n, r);
    var o;
    n = null;
    for (d in l) if (!r.hasOwnProperty(d) && l.hasOwnProperty(d) && l[d] != null) if (d === "style") {
      var u = l[d];
      for (o in u) u.hasOwnProperty(o) && (n || (n = {}), n[o] = "");
    } else d !== "dangerouslySetInnerHTML" && d !== "children" && d !== "suppressContentEditableWarning" && d !== "suppressHydrationWarning" && d !== "autoFocus" && (cr.hasOwnProperty(d) ? i || (i = []) : (i = i || []).push(d, null));
    for (d in r) {
      var s = r[d];
      if (u = l != null ? l[d] : void 0, r.hasOwnProperty(d) && s !== u && (s != null || u != null)) if (d === "style") if (u) {
        for (o in u) !u.hasOwnProperty(o) || s && s.hasOwnProperty(o) || (n || (n = {}), n[o] = "");
        for (o in s) s.hasOwnProperty(o) && u[o] !== s[o] && (n || (n = {}), n[o] = s[o]);
      } else n || (i || (i = []), i.push(
        d,
        n
      )), n = s;
      else d === "dangerouslySetInnerHTML" ? (s = s ? s.__html : void 0, u = u ? u.__html : void 0, s != null && u !== s && (i = i || []).push(d, s)) : d === "children" ? typeof s != "string" && typeof s != "number" || (i = i || []).push(d, "" + s) : d !== "suppressContentEditableWarning" && d !== "suppressHydrationWarning" && (cr.hasOwnProperty(d) ? (s != null && d === "onScroll" && Y("scroll", e), i || u === s || (i = [])) : (i = i || []).push(d, s));
    }
    n && (i = i || []).push("style", n);
    var d = i;
    (t.updateQueue = d) && (t.flags |= 4);
  }
};
Nc = function(e, t, n, r) {
  n !== r && (t.flags |= 4);
};
function Gn(e, t) {
  if (!Z) switch (e.tailMode) {
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
function he(e) {
  var t = e.alternate !== null && e.alternate.child === e.child, n = 0, r = 0;
  if (t) for (var l = e.child; l !== null; ) n |= l.lanes | l.childLanes, r |= l.subtreeFlags & 14680064, r |= l.flags & 14680064, l.return = e, l = l.sibling;
  else for (l = e.child; l !== null; ) n |= l.lanes | l.childLanes, r |= l.subtreeFlags, r |= l.flags, l.return = e, l = l.sibling;
  return e.subtreeFlags |= r, e.childLanes = n, t;
}
function cp(e, t, n) {
  var r = t.pendingProps;
  switch (Bo(t), t.tag) {
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
      return he(t), null;
    case 1:
      return Ne(t.type) && xl(), he(t), null;
    case 3:
      return r = t.stateNode, Tn(), X(Ee), X(ve), bo(), r.pendingContext && (r.context = r.pendingContext, r.pendingContext = null), (e === null || e.child === null) && (Gr(t) ? t.flags |= 4 : e === null || e.memoizedState.isDehydrated && !(t.flags & 256) || (t.flags |= 1024, Xe !== null && (So(Xe), Xe = null))), po(e, t), he(t), null;
    case 5:
      qo(t);
      var l = Yt(_r.current);
      if (n = t.type, e !== null && t.stateNode != null) Ec(e, t, n, r, l), e.ref !== t.ref && (t.flags |= 512, t.flags |= 2097152);
      else {
        if (!r) {
          if (t.stateNode === null) throw Error(w(166));
          return he(t), null;
        }
        if (e = Yt(lt.current), Gr(t)) {
          r = t.stateNode, n = t.type;
          var i = t.memoizedProps;
          switch (r[nt] = t, r[kr] = i, e = (t.mode & 1) !== 0, n) {
            case "dialog":
              Y("cancel", r), Y("close", r);
              break;
            case "iframe":
            case "object":
            case "embed":
              Y("load", r);
              break;
            case "video":
            case "audio":
              for (l = 0; l < bn.length; l++) Y(bn[l], r);
              break;
            case "source":
              Y("error", r);
              break;
            case "img":
            case "image":
            case "link":
              Y(
                "error",
                r
              ), Y("load", r);
              break;
            case "details":
              Y("toggle", r);
              break;
            case "input":
              Cu(r, i), Y("invalid", r);
              break;
            case "select":
              r._wrapperState = { wasMultiple: !!i.multiple }, Y("invalid", r);
              break;
            case "textarea":
              Pu(r, i), Y("invalid", r);
          }
          Ii(n, i), l = null;
          for (var o in i) if (i.hasOwnProperty(o)) {
            var u = i[o];
            o === "children" ? typeof u == "string" ? r.textContent !== u && (i.suppressHydrationWarning !== !0 && Qr(r.textContent, u, e), l = ["children", u]) : typeof u == "number" && r.textContent !== "" + u && (i.suppressHydrationWarning !== !0 && Qr(
              r.textContent,
              u,
              e
            ), l = ["children", "" + u]) : cr.hasOwnProperty(o) && u != null && o === "onScroll" && Y("scroll", r);
          }
          switch (n) {
            case "input":
              Dr(r), ju(r, i, !0);
              break;
            case "textarea":
              Dr(r), Tu(r);
              break;
            case "select":
            case "option":
              break;
            default:
              typeof i.onClick == "function" && (r.onclick = _l);
          }
          r = l, t.updateQueue = r, r !== null && (t.flags |= 4);
        } else {
          o = l.nodeType === 9 ? l : l.ownerDocument, e === "http://www.w3.org/1999/xhtml" && (e = ea(n)), e === "http://www.w3.org/1999/xhtml" ? n === "script" ? (e = o.createElement("div"), e.innerHTML = "<script><\/script>", e = e.removeChild(e.firstChild)) : typeof r.is == "string" ? e = o.createElement(n, { is: r.is }) : (e = o.createElement(n), n === "select" && (o = e, r.multiple ? o.multiple = !0 : r.size && (o.size = r.size))) : e = o.createElementNS(e, n), e[nt] = t, e[kr] = r, zc(e, t, !1, !1), t.stateNode = e;
          e: {
            switch (o = Ui(n, r), n) {
              case "dialog":
                Y("cancel", e), Y("close", e), l = r;
                break;
              case "iframe":
              case "object":
              case "embed":
                Y("load", e), l = r;
                break;
              case "video":
              case "audio":
                for (l = 0; l < bn.length; l++) Y(bn[l], e);
                l = r;
                break;
              case "source":
                Y("error", e), l = r;
                break;
              case "img":
              case "image":
              case "link":
                Y(
                  "error",
                  e
                ), Y("load", e), l = r;
                break;
              case "details":
                Y("toggle", e), l = r;
                break;
              case "input":
                Cu(e, r), l = Mi(e, r), Y("invalid", e);
                break;
              case "option":
                l = r;
                break;
              case "select":
                e._wrapperState = { wasMultiple: !!r.multiple }, l = b({}, r, { value: void 0 }), Y("invalid", e);
                break;
              case "textarea":
                Pu(e, r), l = Ri(e, r), Y("invalid", e);
                break;
              default:
                l = r;
            }
            Ii(n, l), u = l;
            for (i in u) if (u.hasOwnProperty(i)) {
              var s = u[i];
              i === "style" ? ra(e, s) : i === "dangerouslySetInnerHTML" ? (s = s ? s.__html : void 0, s != null && ta(e, s)) : i === "children" ? typeof s == "string" ? (n !== "textarea" || s !== "") && dr(e, s) : typeof s == "number" && dr(e, "" + s) : i !== "suppressContentEditableWarning" && i !== "suppressHydrationWarning" && i !== "autoFocus" && (cr.hasOwnProperty(i) ? s != null && i === "onScroll" && Y("scroll", e) : s != null && Po(e, i, s, o));
            }
            switch (n) {
              case "input":
                Dr(e), ju(e, r, !1);
                break;
              case "textarea":
                Dr(e), Tu(e);
                break;
              case "option":
                r.value != null && e.setAttribute("value", "" + Ot(r.value));
                break;
              case "select":
                e.multiple = !!r.multiple, i = r.value, i != null ? kn(e, !!r.multiple, i, !1) : r.defaultValue != null && kn(
                  e,
                  !!r.multiple,
                  r.defaultValue,
                  !0
                );
                break;
              default:
                typeof l.onClick == "function" && (e.onclick = _l);
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
      return he(t), null;
    case 6:
      if (e && t.stateNode != null) Nc(e, t, e.memoizedProps, r);
      else {
        if (typeof r != "string" && t.stateNode === null) throw Error(w(166));
        if (n = Yt(_r.current), Yt(lt.current), Gr(t)) {
          if (r = t.stateNode, n = t.memoizedProps, r[nt] = t, (i = r.nodeValue !== n) && (e = We, e !== null)) switch (e.tag) {
            case 3:
              Qr(r.nodeValue, n, (e.mode & 1) !== 0);
              break;
            case 5:
              e.memoizedProps.suppressHydrationWarning !== !0 && Qr(r.nodeValue, n, (e.mode & 1) !== 0);
          }
          i && (t.flags |= 4);
        } else r = (n.nodeType === 9 ? n : n.ownerDocument).createTextNode(r), r[nt] = t, t.stateNode = r;
      }
      return he(t), null;
    case 13:
      if (X(J), r = t.memoizedState, e === null || e.memoizedState !== null && e.memoizedState.dehydrated !== null) {
        if (Z && Te !== null && t.mode & 1 && !(t.flags & 128)) Ba(), jn(), t.flags |= 98560, i = !1;
        else if (i = Gr(t), r !== null && r.dehydrated !== null) {
          if (e === null) {
            if (!i) throw Error(w(318));
            if (i = t.memoizedState, i = i !== null ? i.dehydrated : null, !i) throw Error(w(317));
            i[nt] = t;
          } else jn(), !(t.flags & 128) && (t.memoizedState = null), t.flags |= 4;
          he(t), i = !1;
        } else Xe !== null && (So(Xe), Xe = null), i = !0;
        if (!i) return t.flags & 65536 ? t : null;
      }
      return t.flags & 128 ? (t.lanes = n, t) : (r = r !== null, r !== (e !== null && e.memoizedState !== null) && r && (t.child.flags |= 8192, t.mode & 1 && (e === null || J.current & 1 ? oe === 0 && (oe = 3) : du())), t.updateQueue !== null && (t.flags |= 4), he(t), null);
    case 4:
      return Tn(), po(e, t), e === null && yr(t.stateNode.containerInfo), he(t), null;
    case 10:
      return Yo(t.type._context), he(t), null;
    case 17:
      return Ne(t.type) && xl(), he(t), null;
    case 19:
      if (X(J), i = t.memoizedState, i === null) return he(t), null;
      if (r = (t.flags & 128) !== 0, o = i.rendering, o === null) if (r) Gn(i, !1);
      else {
        if (oe !== 0 || e !== null && e.flags & 128) for (e = t.child; e !== null; ) {
          if (o = Tl(e), o !== null) {
            for (t.flags |= 128, Gn(i, !1), r = o.updateQueue, r !== null && (t.updateQueue = r, t.flags |= 4), t.subtreeFlags = 0, r = n, n = t.child; n !== null; ) i = n, e = r, i.flags &= 14680066, o = i.alternate, o === null ? (i.childLanes = 0, i.lanes = e, i.child = null, i.subtreeFlags = 0, i.memoizedProps = null, i.memoizedState = null, i.updateQueue = null, i.dependencies = null, i.stateNode = null) : (i.childLanes = o.childLanes, i.lanes = o.lanes, i.child = o.child, i.subtreeFlags = 0, i.deletions = null, i.memoizedProps = o.memoizedProps, i.memoizedState = o.memoizedState, i.updateQueue = o.updateQueue, i.type = o.type, e = o.dependencies, i.dependencies = e === null ? null : { lanes: e.lanes, firstContext: e.firstContext }), n = n.sibling;
            return G(J, J.current & 1 | 2), t.child;
          }
          e = e.sibling;
        }
        i.tail !== null && ne() > Wn && (t.flags |= 128, r = !0, Gn(i, !1), t.lanes = 4194304);
      }
      else {
        if (!r) if (e = Tl(o), e !== null) {
          if (t.flags |= 128, r = !0, n = e.updateQueue, n !== null && (t.updateQueue = n, t.flags |= 4), Gn(i, !0), i.tail === null && i.tailMode === "hidden" && !o.alternate && !Z) return he(t), null;
        } else 2 * ne() - i.renderingStartTime > Wn && n !== 1073741824 && (t.flags |= 128, r = !0, Gn(i, !1), t.lanes = 4194304);
        i.isBackwards ? (o.sibling = t.child, t.child = o) : (n = i.last, n !== null ? n.sibling = o : t.child = o, i.last = o);
      }
      return i.tail !== null ? (t = i.tail, i.rendering = t, i.tail = t.sibling, i.renderingStartTime = ne(), t.sibling = null, n = J.current, G(J, r ? n & 1 | 2 : n & 1), t) : (he(t), null);
    case 22:
    case 23:
      return cu(), r = t.memoizedState !== null, e !== null && e.memoizedState !== null !== r && (t.flags |= 8192), r && t.mode & 1 ? Pe & 1073741824 && (he(t), t.subtreeFlags & 6 && (t.flags |= 8192)) : he(t), null;
    case 24:
      return null;
    case 25:
      return null;
  }
  throw Error(w(156, t.tag));
}
function dp(e, t) {
  switch (Bo(t), t.tag) {
    case 1:
      return Ne(t.type) && xl(), e = t.flags, e & 65536 ? (t.flags = e & -65537 | 128, t) : null;
    case 3:
      return Tn(), X(Ee), X(ve), bo(), e = t.flags, e & 65536 && !(e & 128) ? (t.flags = e & -65537 | 128, t) : null;
    case 5:
      return qo(t), null;
    case 13:
      if (X(J), e = t.memoizedState, e !== null && e.dehydrated !== null) {
        if (t.alternate === null) throw Error(w(340));
        jn();
      }
      return e = t.flags, e & 65536 ? (t.flags = e & -65537 | 128, t) : null;
    case 19:
      return X(J), null;
    case 4:
      return Tn(), null;
    case 10:
      return Yo(t.type._context), null;
    case 22:
    case 23:
      return cu(), null;
    case 24:
      return null;
    default:
      return null;
  }
}
var Zr = !1, ge = !1, fp = typeof WeakSet == "function" ? WeakSet : Set, j = null;
function yn(e, t) {
  var n = e.ref;
  if (n !== null) if (typeof n == "function") try {
    n(null);
  } catch (r) {
    te(e, t, r);
  }
  else n.current = null;
}
function mo(e, t, n) {
  try {
    n();
  } catch (r) {
    te(e, t, r);
  }
}
var ws = !1;
function pp(e, t) {
  if (Zi = wl, e = La(), Vo(e)) {
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
        var o = 0, u = -1, s = -1, d = 0, m = 0, g = e, h = null;
        t: for (; ; ) {
          for (var S; g !== n || l !== 0 && g.nodeType !== 3 || (u = o + l), g !== i || r !== 0 && g.nodeType !== 3 || (s = o + r), g.nodeType === 3 && (o += g.nodeValue.length), (S = g.firstChild) !== null; )
            h = g, g = S;
          for (; ; ) {
            if (g === e) break t;
            if (h === n && ++d === l && (u = o), h === i && ++m === r && (s = o), (S = g.nextSibling) !== null) break;
            g = h, h = g.parentNode;
          }
          g = S;
        }
        n = u === -1 || s === -1 ? null : { start: u, end: s };
      } else n = null;
    }
    n = n || { start: 0, end: 0 };
  } else n = null;
  for (Ji = { focusedElem: e, selectionRange: n }, wl = !1, j = t; j !== null; ) if (t = j, e = t.child, (t.subtreeFlags & 1028) !== 0 && e !== null) e.return = t, j = e;
  else for (; j !== null; ) {
    t = j;
    try {
      var z = t.alternate;
      if (t.flags & 1024) switch (t.tag) {
        case 0:
        case 11:
        case 15:
          break;
        case 1:
          if (z !== null) {
            var _ = z.memoizedProps, B = z.memoizedState, f = t.stateNode, c = f.getSnapshotBeforeUpdate(t.elementType === t.type ? _ : Ge(t.type, _), B);
            f.__reactInternalSnapshotBeforeUpdate = c;
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
          throw Error(w(163));
      }
    } catch (y) {
      te(t, t.return, y);
    }
    if (e = t.sibling, e !== null) {
      e.return = t.return, j = e;
      break;
    }
    j = t.return;
  }
  return z = ws, ws = !1, z;
}
function ur(e, t, n) {
  var r = t.updateQueue;
  if (r = r !== null ? r.lastEffect : null, r !== null) {
    var l = r = r.next;
    do {
      if ((l.tag & e) === e) {
        var i = l.destroy;
        l.destroy = void 0, i !== void 0 && mo(t, n, i);
      }
      l = l.next;
    } while (l !== r);
  }
}
function Gl(e, t) {
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
function ho(e) {
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
function Cc(e) {
  var t = e.alternate;
  t !== null && (e.alternate = null, Cc(t)), e.child = null, e.deletions = null, e.sibling = null, e.tag === 5 && (t = e.stateNode, t !== null && (delete t[nt], delete t[kr], delete t[eo], delete t[Xf], delete t[Zf])), e.stateNode = null, e.return = null, e.dependencies = null, e.memoizedProps = null, e.memoizedState = null, e.pendingProps = null, e.stateNode = null, e.updateQueue = null;
}
function jc(e) {
  return e.tag === 5 || e.tag === 3 || e.tag === 4;
}
function ks(e) {
  e: for (; ; ) {
    for (; e.sibling === null; ) {
      if (e.return === null || jc(e.return)) return null;
      e = e.return;
    }
    for (e.sibling.return = e.return, e = e.sibling; e.tag !== 5 && e.tag !== 6 && e.tag !== 18; ) {
      if (e.flags & 2 || e.child === null || e.tag === 4) continue e;
      e.child.return = e, e = e.child;
    }
    if (!(e.flags & 2)) return e.stateNode;
  }
}
function go(e, t, n) {
  var r = e.tag;
  if (r === 5 || r === 6) e = e.stateNode, t ? n.nodeType === 8 ? n.parentNode.insertBefore(e, t) : n.insertBefore(e, t) : (n.nodeType === 8 ? (t = n.parentNode, t.insertBefore(e, n)) : (t = n, t.appendChild(e)), n = n._reactRootContainer, n != null || t.onclick !== null || (t.onclick = _l));
  else if (r !== 4 && (e = e.child, e !== null)) for (go(e, t, n), e = e.sibling; e !== null; ) go(e, t, n), e = e.sibling;
}
function vo(e, t, n) {
  var r = e.tag;
  if (r === 5 || r === 6) e = e.stateNode, t ? n.insertBefore(e, t) : n.appendChild(e);
  else if (r !== 4 && (e = e.child, e !== null)) for (vo(e, t, n), e = e.sibling; e !== null; ) vo(e, t, n), e = e.sibling;
}
var de = null, Ye = !1;
function wt(e, t, n) {
  for (n = n.child; n !== null; ) Pc(e, t, n), n = n.sibling;
}
function Pc(e, t, n) {
  if (rt && typeof rt.onCommitFiberUnmount == "function") try {
    rt.onCommitFiberUnmount(Il, n);
  } catch {
  }
  switch (n.tag) {
    case 5:
      ge || yn(n, t);
    case 6:
      var r = de, l = Ye;
      de = null, wt(e, t, n), de = r, Ye = l, de !== null && (Ye ? (e = de, n = n.stateNode, e.nodeType === 8 ? e.parentNode.removeChild(n) : e.removeChild(n)) : de.removeChild(n.stateNode));
      break;
    case 18:
      de !== null && (Ye ? (e = de, n = n.stateNode, e.nodeType === 8 ? vi(e.parentNode, n) : e.nodeType === 1 && vi(e, n), hr(e)) : vi(de, n.stateNode));
      break;
    case 4:
      r = de, l = Ye, de = n.stateNode.containerInfo, Ye = !0, wt(e, t, n), de = r, Ye = l;
      break;
    case 0:
    case 11:
    case 14:
    case 15:
      if (!ge && (r = n.updateQueue, r !== null && (r = r.lastEffect, r !== null))) {
        l = r = r.next;
        do {
          var i = l, o = i.destroy;
          i = i.tag, o !== void 0 && (i & 2 || i & 4) && mo(n, t, o), l = l.next;
        } while (l !== r);
      }
      wt(e, t, n);
      break;
    case 1:
      if (!ge && (yn(n, t), r = n.stateNode, typeof r.componentWillUnmount == "function")) try {
        r.props = n.memoizedProps, r.state = n.memoizedState, r.componentWillUnmount();
      } catch (u) {
        te(n, t, u);
      }
      wt(e, t, n);
      break;
    case 21:
      wt(e, t, n);
      break;
    case 22:
      n.mode & 1 ? (ge = (r = ge) || n.memoizedState !== null, wt(e, t, n), ge = r) : wt(e, t, n);
      break;
    default:
      wt(e, t, n);
  }
}
function Ss(e) {
  var t = e.updateQueue;
  if (t !== null) {
    e.updateQueue = null;
    var n = e.stateNode;
    n === null && (n = e.stateNode = new fp()), t.forEach(function(r) {
      var l = _p.bind(null, e, r);
      n.has(r) || (n.add(r), r.then(l, l));
    });
  }
}
function Qe(e, t) {
  var n = t.deletions;
  if (n !== null) for (var r = 0; r < n.length; r++) {
    var l = n[r];
    try {
      var i = e, o = t, u = o;
      e: for (; u !== null; ) {
        switch (u.tag) {
          case 5:
            de = u.stateNode, Ye = !1;
            break e;
          case 3:
            de = u.stateNode.containerInfo, Ye = !0;
            break e;
          case 4:
            de = u.stateNode.containerInfo, Ye = !0;
            break e;
        }
        u = u.return;
      }
      if (de === null) throw Error(w(160));
      Pc(i, o, l), de = null, Ye = !1;
      var s = l.alternate;
      s !== null && (s.return = null), l.return = null;
    } catch (d) {
      te(l, t, d);
    }
  }
  if (t.subtreeFlags & 12854) for (t = t.child; t !== null; ) Tc(t, e), t = t.sibling;
}
function Tc(e, t) {
  var n = e.alternate, r = e.flags;
  switch (e.tag) {
    case 0:
    case 11:
    case 14:
    case 15:
      if (Qe(t, e), et(e), r & 4) {
        try {
          ur(3, e, e.return), Gl(3, e);
        } catch (_) {
          te(e, e.return, _);
        }
        try {
          ur(5, e, e.return);
        } catch (_) {
          te(e, e.return, _);
        }
      }
      break;
    case 1:
      Qe(t, e), et(e), r & 512 && n !== null && yn(n, n.return);
      break;
    case 5:
      if (Qe(t, e), et(e), r & 512 && n !== null && yn(n, n.return), e.flags & 32) {
        var l = e.stateNode;
        try {
          dr(l, "");
        } catch (_) {
          te(e, e.return, _);
        }
      }
      if (r & 4 && (l = e.stateNode, l != null)) {
        var i = e.memoizedProps, o = n !== null ? n.memoizedProps : i, u = e.type, s = e.updateQueue;
        if (e.updateQueue = null, s !== null) try {
          u === "input" && i.type === "radio" && i.name != null && qs(l, i), Ui(u, o);
          var d = Ui(u, i);
          for (o = 0; o < s.length; o += 2) {
            var m = s[o], g = s[o + 1];
            m === "style" ? ra(l, g) : m === "dangerouslySetInnerHTML" ? ta(l, g) : m === "children" ? dr(l, g) : Po(l, m, g, d);
          }
          switch (u) {
            case "input":
              Oi(l, i);
              break;
            case "textarea":
              bs(l, i);
              break;
            case "select":
              var h = l._wrapperState.wasMultiple;
              l._wrapperState.wasMultiple = !!i.multiple;
              var S = i.value;
              S != null ? kn(l, !!i.multiple, S, !1) : h !== !!i.multiple && (i.defaultValue != null ? kn(
                l,
                !!i.multiple,
                i.defaultValue,
                !0
              ) : kn(l, !!i.multiple, i.multiple ? [] : "", !1));
          }
          l[kr] = i;
        } catch (_) {
          te(e, e.return, _);
        }
      }
      break;
    case 6:
      if (Qe(t, e), et(e), r & 4) {
        if (e.stateNode === null) throw Error(w(162));
        l = e.stateNode, i = e.memoizedProps;
        try {
          l.nodeValue = i;
        } catch (_) {
          te(e, e.return, _);
        }
      }
      break;
    case 3:
      if (Qe(t, e), et(e), r & 4 && n !== null && n.memoizedState.isDehydrated) try {
        hr(t.containerInfo);
      } catch (_) {
        te(e, e.return, _);
      }
      break;
    case 4:
      Qe(t, e), et(e);
      break;
    case 13:
      Qe(t, e), et(e), l = e.child, l.flags & 8192 && (i = l.memoizedState !== null, l.stateNode.isHidden = i, !i || l.alternate !== null && l.alternate.memoizedState !== null || (su = ne())), r & 4 && Ss(e);
      break;
    case 22:
      if (m = n !== null && n.memoizedState !== null, e.mode & 1 ? (ge = (d = ge) || m, Qe(t, e), ge = d) : Qe(t, e), et(e), r & 8192) {
        if (d = e.memoizedState !== null, (e.stateNode.isHidden = d) && !m && e.mode & 1) for (j = e, m = e.child; m !== null; ) {
          for (g = j = m; j !== null; ) {
            switch (h = j, S = h.child, h.tag) {
              case 0:
              case 11:
              case 14:
              case 15:
                ur(4, h, h.return);
                break;
              case 1:
                yn(h, h.return);
                var z = h.stateNode;
                if (typeof z.componentWillUnmount == "function") {
                  r = h, n = h.return;
                  try {
                    t = r, z.props = t.memoizedProps, z.state = t.memoizedState, z.componentWillUnmount();
                  } catch (_) {
                    te(r, n, _);
                  }
                }
                break;
              case 5:
                yn(h, h.return);
                break;
              case 22:
                if (h.memoizedState !== null) {
                  xs(g);
                  continue;
                }
            }
            S !== null ? (S.return = h, j = S) : xs(g);
          }
          m = m.sibling;
        }
        e: for (m = null, g = e; ; ) {
          if (g.tag === 5) {
            if (m === null) {
              m = g;
              try {
                l = g.stateNode, d ? (i = l.style, typeof i.setProperty == "function" ? i.setProperty("display", "none", "important") : i.display = "none") : (u = g.stateNode, s = g.memoizedProps.style, o = s != null && s.hasOwnProperty("display") ? s.display : null, u.style.display = na("display", o));
              } catch (_) {
                te(e, e.return, _);
              }
            }
          } else if (g.tag === 6) {
            if (m === null) try {
              g.stateNode.nodeValue = d ? "" : g.memoizedProps;
            } catch (_) {
              te(e, e.return, _);
            }
          } else if ((g.tag !== 22 && g.tag !== 23 || g.memoizedState === null || g === e) && g.child !== null) {
            g.child.return = g, g = g.child;
            continue;
          }
          if (g === e) break e;
          for (; g.sibling === null; ) {
            if (g.return === null || g.return === e) break e;
            m === g && (m = null), g = g.return;
          }
          m === g && (m = null), g.sibling.return = g.return, g = g.sibling;
        }
      }
      break;
    case 19:
      Qe(t, e), et(e), r & 4 && Ss(e);
      break;
    case 21:
      break;
    default:
      Qe(
        t,
        e
      ), et(e);
  }
}
function et(e) {
  var t = e.flags;
  if (t & 2) {
    try {
      e: {
        for (var n = e.return; n !== null; ) {
          if (jc(n)) {
            var r = n;
            break e;
          }
          n = n.return;
        }
        throw Error(w(160));
      }
      switch (r.tag) {
        case 5:
          var l = r.stateNode;
          r.flags & 32 && (dr(l, ""), r.flags &= -33);
          var i = ks(e);
          vo(e, i, l);
          break;
        case 3:
        case 4:
          var o = r.stateNode.containerInfo, u = ks(e);
          go(e, u, o);
          break;
        default:
          throw Error(w(161));
      }
    } catch (s) {
      te(e, e.return, s);
    }
    e.flags &= -3;
  }
  t & 4096 && (e.flags &= -4097);
}
function mp(e, t, n) {
  j = e, Lc(e);
}
function Lc(e, t, n) {
  for (var r = (e.mode & 1) !== 0; j !== null; ) {
    var l = j, i = l.child;
    if (l.tag === 22 && r) {
      var o = l.memoizedState !== null || Zr;
      if (!o) {
        var u = l.alternate, s = u !== null && u.memoizedState !== null || ge;
        u = Zr;
        var d = ge;
        if (Zr = o, (ge = s) && !d) for (j = l; j !== null; ) o = j, s = o.child, o.tag === 22 && o.memoizedState !== null ? zs(l) : s !== null ? (s.return = o, j = s) : zs(l);
        for (; i !== null; ) j = i, Lc(i), i = i.sibling;
        j = l, Zr = u, ge = d;
      }
      _s(e);
    } else l.subtreeFlags & 8772 && i !== null ? (i.return = l, j = i) : _s(e);
  }
}
function _s(e) {
  for (; j !== null; ) {
    var t = j;
    if (t.flags & 8772) {
      var n = t.alternate;
      try {
        if (t.flags & 8772) switch (t.tag) {
          case 0:
          case 11:
          case 15:
            ge || Gl(5, t);
            break;
          case 1:
            var r = t.stateNode;
            if (t.flags & 4 && !ge) if (n === null) r.componentDidMount();
            else {
              var l = t.elementType === t.type ? n.memoizedProps : Ge(t.type, n.memoizedProps);
              r.componentDidUpdate(l, n.memoizedState, r.__reactInternalSnapshotBeforeUpdate);
            }
            var i = t.updateQueue;
            i !== null && os(t, i, r);
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
              os(t, o, n);
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
              var d = t.alternate;
              if (d !== null) {
                var m = d.memoizedState;
                if (m !== null) {
                  var g = m.dehydrated;
                  g !== null && hr(g);
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
            throw Error(w(163));
        }
        ge || t.flags & 512 && ho(t);
      } catch (h) {
        te(t, t.return, h);
      }
    }
    if (t === e) {
      j = null;
      break;
    }
    if (n = t.sibling, n !== null) {
      n.return = t.return, j = n;
      break;
    }
    j = t.return;
  }
}
function xs(e) {
  for (; j !== null; ) {
    var t = j;
    if (t === e) {
      j = null;
      break;
    }
    var n = t.sibling;
    if (n !== null) {
      n.return = t.return, j = n;
      break;
    }
    j = t.return;
  }
}
function zs(e) {
  for (; j !== null; ) {
    var t = j;
    try {
      switch (t.tag) {
        case 0:
        case 11:
        case 15:
          var n = t.return;
          try {
            Gl(4, t);
          } catch (s) {
            te(t, n, s);
          }
          break;
        case 1:
          var r = t.stateNode;
          if (typeof r.componentDidMount == "function") {
            var l = t.return;
            try {
              r.componentDidMount();
            } catch (s) {
              te(t, l, s);
            }
          }
          var i = t.return;
          try {
            ho(t);
          } catch (s) {
            te(t, i, s);
          }
          break;
        case 5:
          var o = t.return;
          try {
            ho(t);
          } catch (s) {
            te(t, o, s);
          }
      }
    } catch (s) {
      te(t, t.return, s);
    }
    if (t === e) {
      j = null;
      break;
    }
    var u = t.sibling;
    if (u !== null) {
      u.return = t.return, j = u;
      break;
    }
    j = t.return;
  }
}
var hp = Math.ceil, Fl = gt.ReactCurrentDispatcher, ou = gt.ReactCurrentOwner, $e = gt.ReactCurrentBatchConfig, $ = 0, ce = null, le = null, fe = 0, Pe = 0, wn = Dt(0), oe = 0, Nr = null, en = 0, Yl = 0, uu = 0, sr = null, xe = null, su = 0, Wn = 1 / 0, ut = null, Ml = !1, yo = null, Wt = null, Jr = !1, Nt = null, Ol = 0, ar = 0, wo = null, cl = -1, dl = 0;
function we() {
  return $ & 6 ? ne() : cl !== -1 ? cl : cl = ne();
}
function Ft(e) {
  return e.mode & 1 ? $ & 2 && fe !== 0 ? fe & -fe : qf.transition !== null ? (dl === 0 && (dl = ha()), dl) : (e = V, e !== 0 || (e = window.event, e = e === void 0 ? 16 : _a(e.type)), e) : 1;
}
function Je(e, t, n, r) {
  if (50 < ar) throw ar = 0, wo = null, Error(w(185));
  jr(e, n, r), (!($ & 2) || e !== ce) && (e === ce && (!($ & 2) && (Yl |= n), oe === 4 && zt(e, fe)), Ce(e, r), n === 1 && $ === 0 && !(t.mode & 1) && (Wn = ne() + 500, Bl && It()));
}
function Ce(e, t) {
  var n = e.callbackNode;
  Jd(e, t);
  var r = yl(e, e === ce ? fe : 0);
  if (r === 0) n !== null && Fu(n), e.callbackNode = null, e.callbackPriority = 0;
  else if (t = r & -r, e.callbackPriority !== t) {
    if (n != null && Fu(n), t === 1) e.tag === 0 ? Jf(Es.bind(null, e)) : $a(Es.bind(null, e)), Gf(function() {
      !($ & 6) && It();
    }), n = null;
    else {
      switch (ga(r)) {
        case 1:
          n = Mo;
          break;
        case 4:
          n = pa;
          break;
        case 16:
          n = vl;
          break;
        case 536870912:
          n = ma;
          break;
        default:
          n = vl;
      }
      n = Ic(n, Wc.bind(null, e));
    }
    e.callbackPriority = t, e.callbackNode = n;
  }
}
function Wc(e, t) {
  if (cl = -1, dl = 0, $ & 6) throw Error(w(327));
  var n = e.callbackNode;
  if (En() && e.callbackNode !== n) return null;
  var r = yl(e, e === ce ? fe : 0);
  if (r === 0) return null;
  if (r & 30 || r & e.expiredLanes || t) t = Al(e, r);
  else {
    t = r;
    var l = $;
    $ |= 2;
    var i = Mc();
    (ce !== e || fe !== t) && (ut = null, Wn = ne() + 500, Xt(e, t));
    do
      try {
        yp();
        break;
      } catch (u) {
        Fc(e, u);
      }
    while (!0);
    Go(), Fl.current = i, $ = l, le !== null ? t = 0 : (ce = null, fe = 0, t = oe);
  }
  if (t !== 0) {
    if (t === 2 && (l = Hi(e), l !== 0 && (r = l, t = ko(e, l))), t === 1) throw n = Nr, Xt(e, 0), zt(e, r), Ce(e, ne()), n;
    if (t === 6) zt(e, r);
    else {
      if (l = e.current.alternate, !(r & 30) && !gp(l) && (t = Al(e, r), t === 2 && (i = Hi(e), i !== 0 && (r = i, t = ko(e, i))), t === 1)) throw n = Nr, Xt(e, 0), zt(e, r), Ce(e, ne()), n;
      switch (e.finishedWork = l, e.finishedLanes = r, t) {
        case 0:
        case 1:
          throw Error(w(345));
        case 2:
          Bt(e, xe, ut);
          break;
        case 3:
          if (zt(e, r), (r & 130023424) === r && (t = su + 500 - ne(), 10 < t)) {
            if (yl(e, 0) !== 0) break;
            if (l = e.suspendedLanes, (l & r) !== r) {
              we(), e.pingedLanes |= e.suspendedLanes & l;
              break;
            }
            e.timeoutHandle = bi(Bt.bind(null, e, xe, ut), t);
            break;
          }
          Bt(e, xe, ut);
          break;
        case 4:
          if (zt(e, r), (r & 4194240) === r) break;
          for (t = e.eventTimes, l = -1; 0 < r; ) {
            var o = 31 - Ze(r);
            i = 1 << o, o = t[o], o > l && (l = o), r &= ~i;
          }
          if (r = l, r = ne() - r, r = (120 > r ? 120 : 480 > r ? 480 : 1080 > r ? 1080 : 1920 > r ? 1920 : 3e3 > r ? 3e3 : 4320 > r ? 4320 : 1960 * hp(r / 1960)) - r, 10 < r) {
            e.timeoutHandle = bi(Bt.bind(null, e, xe, ut), r);
            break;
          }
          Bt(e, xe, ut);
          break;
        case 5:
          Bt(e, xe, ut);
          break;
        default:
          throw Error(w(329));
      }
    }
  }
  return Ce(e, ne()), e.callbackNode === n ? Wc.bind(null, e) : null;
}
function ko(e, t) {
  var n = sr;
  return e.current.memoizedState.isDehydrated && (Xt(e, t).flags |= 256), e = Al(e, t), e !== 2 && (t = xe, xe = n, t !== null && So(t)), e;
}
function So(e) {
  xe === null ? xe = e : xe.push.apply(xe, e);
}
function gp(e) {
  for (var t = e; ; ) {
    if (t.flags & 16384) {
      var n = t.updateQueue;
      if (n !== null && (n = n.stores, n !== null)) for (var r = 0; r < n.length; r++) {
        var l = n[r], i = l.getSnapshot;
        l = l.value;
        try {
          if (!qe(i(), l)) return !1;
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
function zt(e, t) {
  for (t &= ~uu, t &= ~Yl, e.suspendedLanes |= t, e.pingedLanes &= ~t, e = e.expirationTimes; 0 < t; ) {
    var n = 31 - Ze(t), r = 1 << n;
    e[n] = -1, t &= ~r;
  }
}
function Es(e) {
  if ($ & 6) throw Error(w(327));
  En();
  var t = yl(e, 0);
  if (!(t & 1)) return Ce(e, ne()), null;
  var n = Al(e, t);
  if (e.tag !== 0 && n === 2) {
    var r = Hi(e);
    r !== 0 && (t = r, n = ko(e, r));
  }
  if (n === 1) throw n = Nr, Xt(e, 0), zt(e, t), Ce(e, ne()), n;
  if (n === 6) throw Error(w(345));
  return e.finishedWork = e.current.alternate, e.finishedLanes = t, Bt(e, xe, ut), Ce(e, ne()), null;
}
function au(e, t) {
  var n = $;
  $ |= 1;
  try {
    return e(t);
  } finally {
    $ = n, $ === 0 && (Wn = ne() + 500, Bl && It());
  }
}
function tn(e) {
  Nt !== null && Nt.tag === 0 && !($ & 6) && En();
  var t = $;
  $ |= 1;
  var n = $e.transition, r = V;
  try {
    if ($e.transition = null, V = 1, e) return e();
  } finally {
    V = r, $e.transition = n, $ = t, !($ & 6) && It();
  }
}
function cu() {
  Pe = wn.current, X(wn);
}
function Xt(e, t) {
  e.finishedWork = null, e.finishedLanes = 0;
  var n = e.timeoutHandle;
  if (n !== -1 && (e.timeoutHandle = -1, Qf(n)), le !== null) for (n = le.return; n !== null; ) {
    var r = n;
    switch (Bo(r), r.tag) {
      case 1:
        r = r.type.childContextTypes, r != null && xl();
        break;
      case 3:
        Tn(), X(Ee), X(ve), bo();
        break;
      case 5:
        qo(r);
        break;
      case 4:
        Tn();
        break;
      case 13:
        X(J);
        break;
      case 19:
        X(J);
        break;
      case 10:
        Yo(r.type._context);
        break;
      case 22:
      case 23:
        cu();
    }
    n = n.return;
  }
  if (ce = e, le = e = Mt(e.current, null), fe = Pe = t, oe = 0, Nr = null, uu = Yl = en = 0, xe = sr = null, Gt !== null) {
    for (t = 0; t < Gt.length; t++) if (n = Gt[t], r = n.interleaved, r !== null) {
      n.interleaved = null;
      var l = r.next, i = n.pending;
      if (i !== null) {
        var o = i.next;
        i.next = l, r.next = o;
      }
      n.pending = r;
    }
    Gt = null;
  }
  return e;
}
function Fc(e, t) {
  do {
    var n = le;
    try {
      if (Go(), ul.current = Wl, Ll) {
        for (var r = q.memoizedState; r !== null; ) {
          var l = r.queue;
          l !== null && (l.pending = null), r = r.next;
        }
        Ll = !1;
      }
      if (bt = 0, ae = ie = q = null, or = !1, xr = 0, ou.current = null, n === null || n.return === null) {
        oe = 1, Nr = t, le = null;
        break;
      }
      e: {
        var i = e, o = n.return, u = n, s = t;
        if (t = fe, u.flags |= 32768, s !== null && typeof s == "object" && typeof s.then == "function") {
          var d = s, m = u, g = m.tag;
          if (!(m.mode & 1) && (g === 0 || g === 11 || g === 15)) {
            var h = m.alternate;
            h ? (m.updateQueue = h.updateQueue, m.memoizedState = h.memoizedState, m.lanes = h.lanes) : (m.updateQueue = null, m.memoizedState = null);
          }
          var S = fs(o);
          if (S !== null) {
            S.flags &= -257, ps(S, o, u, i, t), S.mode & 1 && ds(i, d, t), t = S, s = d;
            var z = t.updateQueue;
            if (z === null) {
              var _ = /* @__PURE__ */ new Set();
              _.add(s), t.updateQueue = _;
            } else z.add(s);
            break e;
          } else {
            if (!(t & 1)) {
              ds(i, d, t), du();
              break e;
            }
            s = Error(w(426));
          }
        } else if (Z && u.mode & 1) {
          var B = fs(o);
          if (B !== null) {
            !(B.flags & 65536) && (B.flags |= 256), ps(B, o, u, i, t), Ho(Ln(s, u));
            break e;
          }
        }
        i = s = Ln(s, u), oe !== 4 && (oe = 2), sr === null ? sr = [i] : sr.push(i), i = o;
        do {
          switch (i.tag) {
            case 3:
              i.flags |= 65536, t &= -t, i.lanes |= t;
              var f = gc(i, s, t);
              is(i, f);
              break e;
            case 1:
              u = s;
              var c = i.type, p = i.stateNode;
              if (!(i.flags & 128) && (typeof c.getDerivedStateFromError == "function" || p !== null && typeof p.componentDidCatch == "function" && (Wt === null || !Wt.has(p)))) {
                i.flags |= 65536, t &= -t, i.lanes |= t;
                var y = vc(i, u, t);
                is(i, y);
                break e;
              }
          }
          i = i.return;
        } while (i !== null);
      }
      Ac(n);
    } catch (C) {
      t = C, le === n && n !== null && (le = n = n.return);
      continue;
    }
    break;
  } while (!0);
}
function Mc() {
  var e = Fl.current;
  return Fl.current = Wl, e === null ? Wl : e;
}
function du() {
  (oe === 0 || oe === 3 || oe === 2) && (oe = 4), ce === null || !(en & 268435455) && !(Yl & 268435455) || zt(ce, fe);
}
function Al(e, t) {
  var n = $;
  $ |= 2;
  var r = Mc();
  (ce !== e || fe !== t) && (ut = null, Xt(e, t));
  do
    try {
      vp();
      break;
    } catch (l) {
      Fc(e, l);
    }
  while (!0);
  if (Go(), $ = n, Fl.current = r, le !== null) throw Error(w(261));
  return ce = null, fe = 0, oe;
}
function vp() {
  for (; le !== null; ) Oc(le);
}
function yp() {
  for (; le !== null && !Vd(); ) Oc(le);
}
function Oc(e) {
  var t = Dc(e.alternate, e, Pe);
  e.memoizedProps = e.pendingProps, t === null ? Ac(e) : le = t, ou.current = null;
}
function Ac(e) {
  var t = e;
  do {
    var n = t.alternate;
    if (e = t.return, t.flags & 32768) {
      if (n = dp(n, t), n !== null) {
        n.flags &= 32767, le = n;
        return;
      }
      if (e !== null) e.flags |= 32768, e.subtreeFlags = 0, e.deletions = null;
      else {
        oe = 6, le = null;
        return;
      }
    } else if (n = cp(n, t, Pe), n !== null) {
      le = n;
      return;
    }
    if (t = t.sibling, t !== null) {
      le = t;
      return;
    }
    le = t = e;
  } while (t !== null);
  oe === 0 && (oe = 5);
}
function Bt(e, t, n) {
  var r = V, l = $e.transition;
  try {
    $e.transition = null, V = 1, wp(e, t, n, r);
  } finally {
    $e.transition = l, V = r;
  }
  return null;
}
function wp(e, t, n, r) {
  do
    En();
  while (Nt !== null);
  if ($ & 6) throw Error(w(327));
  n = e.finishedWork;
  var l = e.finishedLanes;
  if (n === null) return null;
  if (e.finishedWork = null, e.finishedLanes = 0, n === e.current) throw Error(w(177));
  e.callbackNode = null, e.callbackPriority = 0;
  var i = n.lanes | n.childLanes;
  if (qd(e, i), e === ce && (le = ce = null, fe = 0), !(n.subtreeFlags & 2064) && !(n.flags & 2064) || Jr || (Jr = !0, Ic(vl, function() {
    return En(), null;
  })), i = (n.flags & 15990) !== 0, n.subtreeFlags & 15990 || i) {
    i = $e.transition, $e.transition = null;
    var o = V;
    V = 1;
    var u = $;
    $ |= 4, ou.current = null, pp(e, n), Tc(n, e), If(Ji), wl = !!Zi, Ji = Zi = null, e.current = n, mp(n), Kd(), $ = u, V = o, $e.transition = i;
  } else e.current = n;
  if (Jr && (Jr = !1, Nt = e, Ol = l), i = e.pendingLanes, i === 0 && (Wt = null), Qd(n.stateNode), Ce(e, ne()), t !== null) for (r = e.onRecoverableError, n = 0; n < t.length; n++) l = t[n], r(l.value, { componentStack: l.stack, digest: l.digest });
  if (Ml) throw Ml = !1, e = yo, yo = null, e;
  return Ol & 1 && e.tag !== 0 && En(), i = e.pendingLanes, i & 1 ? e === wo ? ar++ : (ar = 0, wo = e) : ar = 0, It(), null;
}
function En() {
  if (Nt !== null) {
    var e = ga(Ol), t = $e.transition, n = V;
    try {
      if ($e.transition = null, V = 16 > e ? 16 : e, Nt === null) var r = !1;
      else {
        if (e = Nt, Nt = null, Ol = 0, $ & 6) throw Error(w(331));
        var l = $;
        for ($ |= 4, j = e.current; j !== null; ) {
          var i = j, o = i.child;
          if (j.flags & 16) {
            var u = i.deletions;
            if (u !== null) {
              for (var s = 0; s < u.length; s++) {
                var d = u[s];
                for (j = d; j !== null; ) {
                  var m = j;
                  switch (m.tag) {
                    case 0:
                    case 11:
                    case 15:
                      ur(8, m, i);
                  }
                  var g = m.child;
                  if (g !== null) g.return = m, j = g;
                  else for (; j !== null; ) {
                    m = j;
                    var h = m.sibling, S = m.return;
                    if (Cc(m), m === d) {
                      j = null;
                      break;
                    }
                    if (h !== null) {
                      h.return = S, j = h;
                      break;
                    }
                    j = S;
                  }
                }
              }
              var z = i.alternate;
              if (z !== null) {
                var _ = z.child;
                if (_ !== null) {
                  z.child = null;
                  do {
                    var B = _.sibling;
                    _.sibling = null, _ = B;
                  } while (_ !== null);
                }
              }
              j = i;
            }
          }
          if (i.subtreeFlags & 2064 && o !== null) o.return = i, j = o;
          else e: for (; j !== null; ) {
            if (i = j, i.flags & 2048) switch (i.tag) {
              case 0:
              case 11:
              case 15:
                ur(9, i, i.return);
            }
            var f = i.sibling;
            if (f !== null) {
              f.return = i.return, j = f;
              break e;
            }
            j = i.return;
          }
        }
        var c = e.current;
        for (j = c; j !== null; ) {
          o = j;
          var p = o.child;
          if (o.subtreeFlags & 2064 && p !== null) p.return = o, j = p;
          else e: for (o = c; j !== null; ) {
            if (u = j, u.flags & 2048) try {
              switch (u.tag) {
                case 0:
                case 11:
                case 15:
                  Gl(9, u);
              }
            } catch (C) {
              te(u, u.return, C);
            }
            if (u === o) {
              j = null;
              break e;
            }
            var y = u.sibling;
            if (y !== null) {
              y.return = u.return, j = y;
              break e;
            }
            j = u.return;
          }
        }
        if ($ = l, It(), rt && typeof rt.onPostCommitFiberRoot == "function") try {
          rt.onPostCommitFiberRoot(Il, e);
        } catch {
        }
        r = !0;
      }
      return r;
    } finally {
      V = n, $e.transition = t;
    }
  }
  return !1;
}
function Ns(e, t, n) {
  t = Ln(n, t), t = gc(e, t, 1), e = Lt(e, t, 1), t = we(), e !== null && (jr(e, 1, t), Ce(e, t));
}
function te(e, t, n) {
  if (e.tag === 3) Ns(e, e, n);
  else for (; t !== null; ) {
    if (t.tag === 3) {
      Ns(t, e, n);
      break;
    } else if (t.tag === 1) {
      var r = t.stateNode;
      if (typeof t.type.getDerivedStateFromError == "function" || typeof r.componentDidCatch == "function" && (Wt === null || !Wt.has(r))) {
        e = Ln(n, e), e = vc(t, e, 1), t = Lt(t, e, 1), e = we(), t !== null && (jr(t, 1, e), Ce(t, e));
        break;
      }
    }
    t = t.return;
  }
}
function kp(e, t, n) {
  var r = e.pingCache;
  r !== null && r.delete(t), t = we(), e.pingedLanes |= e.suspendedLanes & n, ce === e && (fe & n) === n && (oe === 4 || oe === 3 && (fe & 130023424) === fe && 500 > ne() - su ? Xt(e, 0) : uu |= n), Ce(e, t);
}
function Rc(e, t) {
  t === 0 && (e.mode & 1 ? (t = $r, $r <<= 1, !($r & 130023424) && ($r = 4194304)) : t = 1);
  var n = we();
  e = mt(e, t), e !== null && (jr(e, t, n), Ce(e, n));
}
function Sp(e) {
  var t = e.memoizedState, n = 0;
  t !== null && (n = t.retryLane), Rc(e, n);
}
function _p(e, t) {
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
      throw Error(w(314));
  }
  r !== null && r.delete(t), Rc(e, n);
}
var Dc;
Dc = function(e, t, n) {
  if (e !== null) if (e.memoizedProps !== t.pendingProps || Ee.current) ze = !0;
  else {
    if (!(e.lanes & n) && !(t.flags & 128)) return ze = !1, ap(e, t, n);
    ze = !!(e.flags & 131072);
  }
  else ze = !1, Z && t.flags & 1048576 && Va(t, Nl, t.index);
  switch (t.lanes = 0, t.tag) {
    case 2:
      var r = t.type;
      al(e, t), e = t.pendingProps;
      var l = Cn(t, ve.current);
      zn(t, n), l = tu(null, t, r, e, l, n);
      var i = nu();
      return t.flags |= 1, typeof l == "object" && l !== null && typeof l.render == "function" && l.$$typeof === void 0 ? (t.tag = 1, t.memoizedState = null, t.updateQueue = null, Ne(r) ? (i = !0, zl(t)) : i = !1, t.memoizedState = l.state !== null && l.state !== void 0 ? l.state : null, Zo(t), l.updater = Ql, t.stateNode = l, l._reactInternals = t, oo(t, r, e, n), t = ao(null, t, r, !0, i, n)) : (t.tag = 0, Z && i && Ko(t), ye(null, t, l, n), t = t.child), t;
    case 16:
      r = t.elementType;
      e: {
        switch (al(e, t), e = t.pendingProps, l = r._init, r = l(r._payload), t.type = r, l = t.tag = zp(r), e = Ge(r, e), l) {
          case 0:
            t = so(null, t, r, e, n);
            break e;
          case 1:
            t = gs(null, t, r, e, n);
            break e;
          case 11:
            t = ms(null, t, r, e, n);
            break e;
          case 14:
            t = hs(null, t, r, Ge(r.type, e), n);
            break e;
        }
        throw Error(w(
          306,
          r,
          ""
        ));
      }
      return t;
    case 0:
      return r = t.type, l = t.pendingProps, l = t.elementType === r ? l : Ge(r, l), so(e, t, r, l, n);
    case 1:
      return r = t.type, l = t.pendingProps, l = t.elementType === r ? l : Ge(r, l), gs(e, t, r, l, n);
    case 3:
      e: {
        if (Sc(t), e === null) throw Error(w(387));
        r = t.pendingProps, i = t.memoizedState, l = i.element, Ya(e, t), Pl(t, r, null, n);
        var o = t.memoizedState;
        if (r = o.element, i.isDehydrated) if (i = { element: r, isDehydrated: !1, cache: o.cache, pendingSuspenseBoundaries: o.pendingSuspenseBoundaries, transitions: o.transitions }, t.updateQueue.baseState = i, t.memoizedState = i, t.flags & 256) {
          l = Ln(Error(w(423)), t), t = vs(e, t, r, n, l);
          break e;
        } else if (r !== l) {
          l = Ln(Error(w(424)), t), t = vs(e, t, r, n, l);
          break e;
        } else for (Te = Tt(t.stateNode.containerInfo.firstChild), We = t, Z = !0, Xe = null, n = Qa(t, null, r, n), t.child = n; n; ) n.flags = n.flags & -3 | 4096, n = n.sibling;
        else {
          if (jn(), r === l) {
            t = ht(e, t, n);
            break e;
          }
          ye(e, t, r, n);
        }
        t = t.child;
      }
      return t;
    case 5:
      return Xa(t), e === null && ro(t), r = t.type, l = t.pendingProps, i = e !== null ? e.memoizedProps : null, o = l.children, qi(r, l) ? o = null : i !== null && qi(r, i) && (t.flags |= 32), kc(e, t), ye(e, t, o, n), t.child;
    case 6:
      return e === null && ro(t), null;
    case 13:
      return _c(e, t, n);
    case 4:
      return Jo(t, t.stateNode.containerInfo), r = t.pendingProps, e === null ? t.child = Pn(t, null, r, n) : ye(e, t, r, n), t.child;
    case 11:
      return r = t.type, l = t.pendingProps, l = t.elementType === r ? l : Ge(r, l), ms(e, t, r, l, n);
    case 7:
      return ye(e, t, t.pendingProps, n), t.child;
    case 8:
      return ye(e, t, t.pendingProps.children, n), t.child;
    case 12:
      return ye(e, t, t.pendingProps.children, n), t.child;
    case 10:
      e: {
        if (r = t.type._context, l = t.pendingProps, i = t.memoizedProps, o = l.value, G(Cl, r._currentValue), r._currentValue = o, i !== null) if (qe(i.value, o)) {
          if (i.children === l.children && !Ee.current) {
            t = ht(e, t, n);
            break e;
          }
        } else for (i = t.child, i !== null && (i.return = t); i !== null; ) {
          var u = i.dependencies;
          if (u !== null) {
            o = i.child;
            for (var s = u.firstContext; s !== null; ) {
              if (s.context === r) {
                if (i.tag === 1) {
                  s = dt(-1, n & -n), s.tag = 2;
                  var d = i.updateQueue;
                  if (d !== null) {
                    d = d.shared;
                    var m = d.pending;
                    m === null ? s.next = s : (s.next = m.next, m.next = s), d.pending = s;
                  }
                }
                i.lanes |= n, s = i.alternate, s !== null && (s.lanes |= n), lo(
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
            if (o = i.return, o === null) throw Error(w(341));
            o.lanes |= n, u = o.alternate, u !== null && (u.lanes |= n), lo(o, n, t), o = i.sibling;
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
        ye(e, t, l.children, n), t = t.child;
      }
      return t;
    case 9:
      return l = t.type, r = t.pendingProps.children, zn(t, n), l = Ve(l), r = r(l), t.flags |= 1, ye(e, t, r, n), t.child;
    case 14:
      return r = t.type, l = Ge(r, t.pendingProps), l = Ge(r.type, l), hs(e, t, r, l, n);
    case 15:
      return yc(e, t, t.type, t.pendingProps, n);
    case 17:
      return r = t.type, l = t.pendingProps, l = t.elementType === r ? l : Ge(r, l), al(e, t), t.tag = 1, Ne(r) ? (e = !0, zl(t)) : e = !1, zn(t, n), hc(t, r, l), oo(t, r, l, n), ao(null, t, r, !0, e, n);
    case 19:
      return xc(e, t, n);
    case 22:
      return wc(e, t, n);
  }
  throw Error(w(156, t.tag));
};
function Ic(e, t) {
  return fa(e, t);
}
function xp(e, t, n, r) {
  this.tag = e, this.key = n, this.sibling = this.child = this.return = this.stateNode = this.type = this.elementType = null, this.index = 0, this.ref = null, this.pendingProps = t, this.dependencies = this.memoizedState = this.updateQueue = this.memoizedProps = null, this.mode = r, this.subtreeFlags = this.flags = 0, this.deletions = null, this.childLanes = this.lanes = 0, this.alternate = null;
}
function Ue(e, t, n, r) {
  return new xp(e, t, n, r);
}
function fu(e) {
  return e = e.prototype, !(!e || !e.isReactComponent);
}
function zp(e) {
  if (typeof e == "function") return fu(e) ? 1 : 0;
  if (e != null) {
    if (e = e.$$typeof, e === Lo) return 11;
    if (e === Wo) return 14;
  }
  return 2;
}
function Mt(e, t) {
  var n = e.alternate;
  return n === null ? (n = Ue(e.tag, t, e.key, e.mode), n.elementType = e.elementType, n.type = e.type, n.stateNode = e.stateNode, n.alternate = e, e.alternate = n) : (n.pendingProps = t, n.type = e.type, n.flags = 0, n.subtreeFlags = 0, n.deletions = null), n.flags = e.flags & 14680064, n.childLanes = e.childLanes, n.lanes = e.lanes, n.child = e.child, n.memoizedProps = e.memoizedProps, n.memoizedState = e.memoizedState, n.updateQueue = e.updateQueue, t = e.dependencies, n.dependencies = t === null ? null : { lanes: t.lanes, firstContext: t.firstContext }, n.sibling = e.sibling, n.index = e.index, n.ref = e.ref, n;
}
function fl(e, t, n, r, l, i) {
  var o = 2;
  if (r = e, typeof e == "function") fu(e) && (o = 1);
  else if (typeof e == "string") o = 5;
  else e: switch (e) {
    case an:
      return Zt(n.children, l, i, t);
    case To:
      o = 8, l |= 8;
      break;
    case Ti:
      return e = Ue(12, n, t, l | 2), e.elementType = Ti, e.lanes = i, e;
    case Li:
      return e = Ue(13, n, t, l), e.elementType = Li, e.lanes = i, e;
    case Wi:
      return e = Ue(19, n, t, l), e.elementType = Wi, e.lanes = i, e;
    case Xs:
      return Xl(n, l, i, t);
    default:
      if (typeof e == "object" && e !== null) switch (e.$$typeof) {
        case Gs:
          o = 10;
          break e;
        case Ys:
          o = 9;
          break e;
        case Lo:
          o = 11;
          break e;
        case Wo:
          o = 14;
          break e;
        case St:
          o = 16, r = null;
          break e;
      }
      throw Error(w(130, e == null ? e : typeof e, ""));
  }
  return t = Ue(o, n, t, l), t.elementType = e, t.type = r, t.lanes = i, t;
}
function Zt(e, t, n, r) {
  return e = Ue(7, e, r, t), e.lanes = n, e;
}
function Xl(e, t, n, r) {
  return e = Ue(22, e, r, t), e.elementType = Xs, e.lanes = n, e.stateNode = { isHidden: !1 }, e;
}
function Ei(e, t, n) {
  return e = Ue(6, e, null, t), e.lanes = n, e;
}
function Ni(e, t, n) {
  return t = Ue(4, e.children !== null ? e.children : [], e.key, t), t.lanes = n, t.stateNode = { containerInfo: e.containerInfo, pendingChildren: null, implementation: e.implementation }, t;
}
function Ep(e, t, n, r, l) {
  this.tag = t, this.containerInfo = e, this.finishedWork = this.pingCache = this.current = this.pendingChildren = null, this.timeoutHandle = -1, this.callbackNode = this.pendingContext = this.context = null, this.callbackPriority = 0, this.eventTimes = oi(0), this.expirationTimes = oi(-1), this.entangledLanes = this.finishedLanes = this.mutableReadLanes = this.expiredLanes = this.pingedLanes = this.suspendedLanes = this.pendingLanes = 0, this.entanglements = oi(0), this.identifierPrefix = r, this.onRecoverableError = l, this.mutableSourceEagerHydrationData = null;
}
function pu(e, t, n, r, l, i, o, u, s) {
  return e = new Ep(e, t, n, u, s), t === 1 ? (t = 1, i === !0 && (t |= 8)) : t = 0, i = Ue(3, null, null, t), e.current = i, i.stateNode = e, i.memoizedState = { element: r, isDehydrated: n, cache: null, transitions: null, pendingSuspenseBoundaries: null }, Zo(i), e;
}
function Np(e, t, n) {
  var r = 3 < arguments.length && arguments[3] !== void 0 ? arguments[3] : null;
  return { $$typeof: sn, key: r == null ? null : "" + r, children: e, containerInfo: t, implementation: n };
}
function Uc(e) {
  if (!e) return At;
  e = e._reactInternals;
  e: {
    if (rn(e) !== e || e.tag !== 1) throw Error(w(170));
    var t = e;
    do {
      switch (t.tag) {
        case 3:
          t = t.stateNode.context;
          break e;
        case 1:
          if (Ne(t.type)) {
            t = t.stateNode.__reactInternalMemoizedMergedChildContext;
            break e;
          }
      }
      t = t.return;
    } while (t !== null);
    throw Error(w(171));
  }
  if (e.tag === 1) {
    var n = e.type;
    if (Ne(n)) return Ua(e, n, t);
  }
  return t;
}
function $c(e, t, n, r, l, i, o, u, s) {
  return e = pu(n, r, !0, e, l, i, o, u, s), e.context = Uc(null), n = e.current, r = we(), l = Ft(n), i = dt(r, l), i.callback = t ?? null, Lt(n, i, l), e.current.lanes = l, jr(e, l, r), Ce(e, r), e;
}
function Zl(e, t, n, r) {
  var l = t.current, i = we(), o = Ft(l);
  return n = Uc(n), t.context === null ? t.context = n : t.pendingContext = n, t = dt(i, o), t.payload = { element: e }, r = r === void 0 ? null : r, r !== null && (t.callback = r), e = Lt(l, t, o), e !== null && (Je(e, l, o, i), ol(e, l, o)), o;
}
function Rl(e) {
  if (e = e.current, !e.child) return null;
  switch (e.child.tag) {
    case 5:
      return e.child.stateNode;
    default:
      return e.child.stateNode;
  }
}
function Cs(e, t) {
  if (e = e.memoizedState, e !== null && e.dehydrated !== null) {
    var n = e.retryLane;
    e.retryLane = n !== 0 && n < t ? n : t;
  }
}
function mu(e, t) {
  Cs(e, t), (e = e.alternate) && Cs(e, t);
}
function Cp() {
  return null;
}
var Vc = typeof reportError == "function" ? reportError : function(e) {
  console.error(e);
};
function hu(e) {
  this._internalRoot = e;
}
Jl.prototype.render = hu.prototype.render = function(e) {
  var t = this._internalRoot;
  if (t === null) throw Error(w(409));
  Zl(e, t, null, null);
};
Jl.prototype.unmount = hu.prototype.unmount = function() {
  var e = this._internalRoot;
  if (e !== null) {
    this._internalRoot = null;
    var t = e.containerInfo;
    tn(function() {
      Zl(null, e, null, null);
    }), t[pt] = null;
  }
};
function Jl(e) {
  this._internalRoot = e;
}
Jl.prototype.unstable_scheduleHydration = function(e) {
  if (e) {
    var t = wa();
    e = { blockedOn: null, target: e, priority: t };
    for (var n = 0; n < xt.length && t !== 0 && t < xt[n].priority; n++) ;
    xt.splice(n, 0, e), n === 0 && Sa(e);
  }
};
function gu(e) {
  return !(!e || e.nodeType !== 1 && e.nodeType !== 9 && e.nodeType !== 11);
}
function ql(e) {
  return !(!e || e.nodeType !== 1 && e.nodeType !== 9 && e.nodeType !== 11 && (e.nodeType !== 8 || e.nodeValue !== " react-mount-point-unstable "));
}
function js() {
}
function jp(e, t, n, r, l) {
  if (l) {
    if (typeof r == "function") {
      var i = r;
      r = function() {
        var d = Rl(o);
        i.call(d);
      };
    }
    var o = $c(t, r, e, 0, null, !1, !1, "", js);
    return e._reactRootContainer = o, e[pt] = o.current, yr(e.nodeType === 8 ? e.parentNode : e), tn(), o;
  }
  for (; l = e.lastChild; ) e.removeChild(l);
  if (typeof r == "function") {
    var u = r;
    r = function() {
      var d = Rl(s);
      u.call(d);
    };
  }
  var s = pu(e, 0, !1, null, null, !1, !1, "", js);
  return e._reactRootContainer = s, e[pt] = s.current, yr(e.nodeType === 8 ? e.parentNode : e), tn(function() {
    Zl(t, s, n, r);
  }), s;
}
function bl(e, t, n, r, l) {
  var i = n._reactRootContainer;
  if (i) {
    var o = i;
    if (typeof l == "function") {
      var u = l;
      l = function() {
        var s = Rl(o);
        u.call(s);
      };
    }
    Zl(t, o, e, l);
  } else o = jp(n, t, e, l, r);
  return Rl(o);
}
va = function(e) {
  switch (e.tag) {
    case 3:
      var t = e.stateNode;
      if (t.current.memoizedState.isDehydrated) {
        var n = qn(t.pendingLanes);
        n !== 0 && (Oo(t, n | 1), Ce(t, ne()), !($ & 6) && (Wn = ne() + 500, It()));
      }
      break;
    case 13:
      tn(function() {
        var r = mt(e, 1);
        if (r !== null) {
          var l = we();
          Je(r, e, 1, l);
        }
      }), mu(e, 1);
  }
};
Ao = function(e) {
  if (e.tag === 13) {
    var t = mt(e, 134217728);
    if (t !== null) {
      var n = we();
      Je(t, e, 134217728, n);
    }
    mu(e, 134217728);
  }
};
ya = function(e) {
  if (e.tag === 13) {
    var t = Ft(e), n = mt(e, t);
    if (n !== null) {
      var r = we();
      Je(n, e, t, r);
    }
    mu(e, t);
  }
};
wa = function() {
  return V;
};
ka = function(e, t) {
  var n = V;
  try {
    return V = e, t();
  } finally {
    V = n;
  }
};
Vi = function(e, t, n) {
  switch (t) {
    case "input":
      if (Oi(e, n), t = n.name, n.type === "radio" && t != null) {
        for (n = e; n.parentNode; ) n = n.parentNode;
        for (n = n.querySelectorAll("input[name=" + JSON.stringify("" + t) + '][type="radio"]'), t = 0; t < n.length; t++) {
          var r = n[t];
          if (r !== e && r.form === e.form) {
            var l = Kl(r);
            if (!l) throw Error(w(90));
            Js(r), Oi(r, l);
          }
        }
      }
      break;
    case "textarea":
      bs(e, n);
      break;
    case "select":
      t = n.value, t != null && kn(e, !!n.multiple, t, !1);
  }
};
oa = au;
ua = tn;
var Pp = { usingClientEntryPoint: !1, Events: [Tr, pn, Kl, la, ia, au] }, Yn = { findFiberByHostInstance: Qt, bundleType: 0, version: "18.3.1", rendererPackageName: "react-dom" }, Tp = { bundleType: Yn.bundleType, version: Yn.version, rendererPackageName: Yn.rendererPackageName, rendererConfig: Yn.rendererConfig, overrideHookState: null, overrideHookStateDeletePath: null, overrideHookStateRenamePath: null, overrideProps: null, overridePropsDeletePath: null, overridePropsRenamePath: null, setErrorHandler: null, setSuspenseHandler: null, scheduleUpdate: null, currentDispatcherRef: gt.ReactCurrentDispatcher, findHostInstanceByFiber: function(e) {
  return e = ca(e), e === null ? null : e.stateNode;
}, findFiberByHostInstance: Yn.findFiberByHostInstance || Cp, findHostInstancesForRefresh: null, scheduleRefresh: null, scheduleRoot: null, setRefreshHandler: null, getCurrentFiber: null, reconcilerVersion: "18.3.1-next-f1338f8080-20240426" };
if (typeof __REACT_DEVTOOLS_GLOBAL_HOOK__ < "u") {
  var qr = __REACT_DEVTOOLS_GLOBAL_HOOK__;
  if (!qr.isDisabled && qr.supportsFiber) try {
    Il = qr.inject(Tp), rt = qr;
  } catch {
  }
}
Me.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED = Pp;
Me.createPortal = function(e, t) {
  var n = 2 < arguments.length && arguments[2] !== void 0 ? arguments[2] : null;
  if (!gu(t)) throw Error(w(200));
  return Np(e, t, null, n);
};
Me.createRoot = function(e, t) {
  if (!gu(e)) throw Error(w(299));
  var n = !1, r = "", l = Vc;
  return t != null && (t.unstable_strictMode === !0 && (n = !0), t.identifierPrefix !== void 0 && (r = t.identifierPrefix), t.onRecoverableError !== void 0 && (l = t.onRecoverableError)), t = pu(e, 1, !1, null, null, n, !1, r, l), e[pt] = t.current, yr(e.nodeType === 8 ? e.parentNode : e), new hu(t);
};
Me.findDOMNode = function(e) {
  if (e == null) return null;
  if (e.nodeType === 1) return e;
  var t = e._reactInternals;
  if (t === void 0)
    throw typeof e.render == "function" ? Error(w(188)) : (e = Object.keys(e).join(","), Error(w(268, e)));
  return e = ca(t), e = e === null ? null : e.stateNode, e;
};
Me.flushSync = function(e) {
  return tn(e);
};
Me.hydrate = function(e, t, n) {
  if (!ql(t)) throw Error(w(200));
  return bl(null, e, t, !0, n);
};
Me.hydrateRoot = function(e, t, n) {
  if (!gu(e)) throw Error(w(405));
  var r = n != null && n.hydratedSources || null, l = !1, i = "", o = Vc;
  if (n != null && (n.unstable_strictMode === !0 && (l = !0), n.identifierPrefix !== void 0 && (i = n.identifierPrefix), n.onRecoverableError !== void 0 && (o = n.onRecoverableError)), t = $c(t, null, e, 1, n ?? null, l, !1, i, o), e[pt] = t.current, yr(e), r) for (e = 0; e < r.length; e++) n = r[e], l = n._getVersion, l = l(n._source), t.mutableSourceEagerHydrationData == null ? t.mutableSourceEagerHydrationData = [n, l] : t.mutableSourceEagerHydrationData.push(
    n,
    l
  );
  return new Jl(t);
};
Me.render = function(e, t, n) {
  if (!ql(t)) throw Error(w(200));
  return bl(null, e, t, !1, n);
};
Me.unmountComponentAtNode = function(e) {
  if (!ql(e)) throw Error(w(40));
  return e._reactRootContainer ? (tn(function() {
    bl(null, null, e, !1, function() {
      e._reactRootContainer = null, e[pt] = null;
    });
  }), !0) : !1;
};
Me.unstable_batchedUpdates = au;
Me.unstable_renderSubtreeIntoContainer = function(e, t, n, r) {
  if (!ql(n)) throw Error(w(200));
  if (e == null || e._reactInternals === void 0) throw Error(w(38));
  return bl(e, t, n, !1, r);
};
Me.version = "18.3.1-next-f1338f8080-20240426";
function Kc() {
  if (!(typeof __REACT_DEVTOOLS_GLOBAL_HOOK__ > "u" || typeof __REACT_DEVTOOLS_GLOBAL_HOOK__.checkDCE != "function"))
    try {
      __REACT_DEVTOOLS_GLOBAL_HOOK__.checkDCE(Kc);
    } catch (e) {
      console.error(e);
    }
}
Kc(), Ks.exports = Me;
var Lp = Ks.exports, Bc, Ps = Lp;
Bc = Ps.createRoot, Ps.hydrateRoot;
function vu(e) {
  return e.replace(/\/+$/, "");
}
async function yu(e, t) {
  let n = "";
  try {
    n = await e.text();
  } catch {
    n = "";
  }
  const r = new Error(`${t} failed with ${e.status}${n ? `: ${n.slice(0, 500)}` : ""}`);
  return r.status = e.status, r.body = n, r;
}
function Le(e) {
  return e && typeof e == "object" && !Array.isArray(e) ? e : {};
}
function Wp(e) {
  const t = Le(e), n = [
    t.config,
    t.client,
    t.data,
    t.result,
    t.item,
    t
  ];
  for (const r of n) {
    const l = Le(r);
    if (Object.keys(l).length > 0) return l;
  }
  return t;
}
function A(e, t = "") {
  return e == null ? t : String(e);
}
function wu(e, t = 0) {
  const n = Number(e);
  return Number.isFinite(n) ? n : t;
}
function _o(e, t = !1) {
  return typeof e == "boolean" ? e : typeof e == "number" ? e === 1 : typeof e == "string" ? ["1", "true", "yes", "ja", "on"].includes(e.toLowerCase()) : t;
}
function it(e) {
  if (Array.isArray(e)) return e;
  if (typeof e == "string") {
    const t = e.trim();
    if (!t) return [];
    try {
      const n = JSON.parse(t), r = it(n);
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
      const l = it(t[r]);
      if (l.length > 0) return l;
    }
  }
  return [];
}
function Xn(e, t) {
  for (const n of t) {
    const r = it(e[n]);
    if (r.length > 0) return r;
  }
  return [];
}
function Fp(e) {
  const t = [];
  for (const n of it(e)) {
    const r = Le(n), l = wu(r.id, NaN), i = A(r.name ?? r.title, "");
    !Number.isFinite(l) || !i || t.push({
      id: l,
      name: i,
      slug: typeof r.slug == "string" ? r.slug : void 0,
      icon: typeof r.icon == "string" ? r.icon : void 0
    });
  }
  return t;
}
function Mp(e) {
  const t = Le(e), n = A(t.value ?? t.slug ?? (t.id !== void 0 ? String(t.id) : ""), "").trim(), r = A(t.title ?? t.name, "").trim();
  if (!n || !r) return;
  const l = t.numericValue ?? t.numeric_value, i = l != null ? wu(l, NaN) : NaN;
  return {
    value: n,
    title: r,
    text: A(t.text ?? t.description, "").trim() || void 0,
    icon: A(t.icon, "").trim() || void 0,
    imageUrl: A(t.imageUrl ?? t.image_url, "").trim() || void 0,
    numericValue: Number.isFinite(i) ? i : void 0,
    id: typeof t.id == "number" ? t.id : void 0
  };
}
function Op(e) {
  const t = Le(e), n = A(t.answerKey ?? t.answer_key, "").trim(), r = A(t.title, "").trim();
  if (!n || !r) return;
  const l = A(t.questionType ?? t.question_type, "generic"), i = A(t.fieldType ?? t.field_type, "").trim(), o = it(t.options).map(Mp).filter((u) => !!u);
  return {
    answerKey: n,
    title: r,
    helpText: A(t.helpText ?? t.help_text, "").trim() || null,
    questionType: l,
    multiSelect: _o(t.multiSelect ?? t.multi_select, !1),
    fieldType: i || null,
    required: _o(t.required, !1),
    dependsOnAnswerKey: A(t.dependsOnAnswerKey ?? t.depends_on_answer_key, "").trim() || null,
    dependsOnValue: A(t.dependsOnValue ?? t.depends_on_value, "").trim() || null,
    options: o
  };
}
function Ap(e) {
  const t = [];
  for (const n of it(e)) {
    const r = Le(n), l = A(r.key ?? r.stepKey ?? r.step_key, "").trim(), i = A(r.label ?? r.title, "").trim();
    !l || !i || t.push({
      key: l,
      label: i,
      type: A(r.type, "questions"),
      questions: it(r.questions).map(Op).filter((o) => !!o)
    });
  }
  return t;
}
async function Rp(e, t) {
  if (!e) {
    const u = new Error("No apiBaseUrl configured");
    throw u.status = 0, u;
  }
  const n = `${vu(e)}/api/tfd-partner-wizard/config/${encodeURIComponent(t)}`, r = await fetch(n, {
    method: "GET",
    credentials: "omit",
    headers: {
      Accept: "application/json"
    }
  });
  if (!r.ok)
    throw await yu(r, "Config request");
  const l = await r.json(), i = Wp(l), o = A(i.intro ?? i.introText ?? i.description, "Finde heraus, welches Partner-Modell und welche Tool-Richtung zu dir passt.");
  return {
    clientId: A(i.clientId ?? i.client_id ?? t, t),
    name: A(i.name, ""),
    headline: A(i.headline ?? i.title, "Chiptuning Händler werden"),
    intro: o,
    introText: o,
    primaryColor: A(i.primaryColor ?? i.primary_color, "#E31E24"),
    theme: A(i.theme, "light"),
    toolRequired: _o(i.toolRequired ?? i.tool_required, !0),
    defaultVehiclePrice: wu(i.defaultVehiclePrice ?? i.default_vehicle_price, 0),
    enabledVehicleCategories: Fp(
      Xn(i, ["enabledVehicleCategories", "enabled_vehicle_categories", "vehicleCategories", "vehicle_categories", "vehicles"])
    ),
    enabledServices: Xn(i, ["enabledServices", "enabled_services", "services"]),
    currentToolOptions: Xn(i, ["currentToolOptions", "current_tool_options", "toolOptions", "tool_options", "currentTools", "current_tools"]),
    customLabels: i.customLabels ?? i.custom_labels ?? {},
    privacyNotice: A(
      i.privacyNotice ?? i.privacy_notice,
      "Mit dem Absenden erklärst du dich einverstanden, dass deine Angaben zur Bearbeitung der Anfrage gespeichert werden und wir dich kontaktieren."
    ),
    toolDisclaimer: A(i.toolDisclaimer ?? i.tool_disclaimer, ""),
    ctaLabel: A(i.ctaLabel ?? i.cta_label, "Anfrage senden"),
    contactTarget: A(i.contactTarget ?? i.contact_target, ""),
    tools: Xn(i, ["tools", "toolRecommendations", "tool_recommendations", "recommendedTools"]),
    packages: Xn(i, ["packages", "partnerPackages", "partner_packages"]),
    steps: Ap(i.steps),
    raw: l
  };
}
async function Dp(e, t) {
  if (!e)
    throw new Error("No apiBaseUrl configured");
  const n = `${vu(e)}/api/tfd-partner-wizard/recommend`, r = await fetch(n, {
    method: "POST",
    credentials: "omit",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json"
    },
    body: JSON.stringify(t)
  });
  if (!r.ok)
    throw await yu(r, "Recommendation request");
  const l = await r.json();
  return Kp(l);
}
async function Ip(e, t) {
  if (!e)
    throw new Error("No apiBaseUrl configured");
  const n = `${vu(e)}/api/tfd-partner-wizard/leads`, r = await fetch(n, {
    method: "POST",
    credentials: "omit",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json"
    },
    body: JSON.stringify(t)
  });
  if (!r.ok)
    throw await yu(r, "Lead request");
  try {
    return await r.json();
  } catch {
    return null;
  }
}
function Up(e) {
  return it(e).map((t) => {
    if (typeof t == "string") return t.trim();
    const n = Le(t);
    return A(n.name ?? n.title ?? n.label ?? n.text ?? n.description).trim();
  }).filter(Boolean);
}
function Hc(e) {
  const t = Le(e), n = A(t.title).trim();
  if (n)
    return {
      uid: typeof t.uid == "number" ? t.uid : void 0,
      title: n,
      sku: typeof t.sku == "string" ? t.sku : void 0,
      price: t.price !== void 0 && t.price !== null ? String(t.price) : void 0,
      specialPrice: t.specialPrice !== void 0 && t.specialPrice !== null ? String(t.specialPrice) : void 0,
      slug: typeof t.slug == "string" ? t.slug : void 0,
      imageUrl: A(t.imageUrl ?? t.image_url).trim() || void 0
    };
}
function $p(e) {
  const t = [];
  return it(e).forEach((n) => {
    const r = Hc(n);
    r && t.push(r);
  }), t;
}
function Vp(e) {
  const t = [];
  return it(e).forEach((n, r) => {
    if (typeof n == "string") {
      const s = n.trim();
      s && t.push({ key: `tool-${r}`, name: s });
      return;
    }
    const l = Le(n), i = A(l.name ?? l.title ?? l.label).trim();
    if (!i) return;
    const o = Hc(l.product), u = A(l.imageUrl ?? l.image_url).trim() || (o == null ? void 0 : o.imageUrl);
    t.push({
      key: A(l.key ?? l.toolKey ?? `tool-${r}`),
      name: i,
      description: A(l.description ?? l.text, "").trim() || void 0,
      level: typeof l.level == "string" ? l.level : void 0,
      imageUrl: u,
      product: o,
      fromCombination: l.fromCombination === !0
    });
  }), t;
}
function Kp(e) {
  const t = Le(e), n = Le(t.recommendation), r = Le(t.result), l = Object.keys(n).length > 0 ? n : Object.keys(r).length > 0 ? r : t, i = Le(l.package ?? l.recommendedPackage ?? l.packageData ?? l.recommended_package), o = l.packageName ?? l.packageTitle ?? l.package_title ?? l.recommendedPackageTitle ?? l.recommended_package_title ?? l.title ?? l.name ?? i.name ?? i.title ?? i.packageTitle ?? "Partner Empfehlung", u = l.packageKey ?? l.package_key ?? l.key ?? l.slug ?? i.key ?? i.slug ?? A(o).toLowerCase().replace(/[^a-z0-9]+/gi, "-"), s = Up(
    l.bullets ?? l.items ?? l.benefits ?? l.packageFeatures ?? l.package_features ?? l.features ?? i.bullets ?? i.benefits ?? i.features ?? []
  ), d = Vp(
    l.recommendedTools ?? l.recommended_tools ?? l.tools ?? l.toolRecommendations ?? l.tool_recommendations ?? []
  ), m = $p(
    l.recommendedProducts ?? l.recommended_products ?? []
  ), h = A(l.status ?? l.recommendationStatus ?? l.recommendation_status, "ready") === "tool_required" || l.toolRequired === !0 ? "tool_required" : "ready";
  return {
    status: h,
    packageKey: A(u),
    packageName: A(o),
    leadStatus: h === "tool_required" ? "tool_consulting" : "new_partner_lead",
    cta: A(
      l.cta ?? l.ctaLabel ?? l.cta_label ?? l.nextStep ?? l.next_step ?? i.ctaLabel,
      h === "tool_required" ? "Tool-Beratung anfragen" : "Anfrage senden"
    ),
    summary: A(
      l.summary ?? l.packageText ?? l.package_text ?? l.shortDescription ?? l.description ?? i.shortDescription ?? i.description,
      "Auf Basis deiner Angaben bereiten wir die passende Empfehlung vor."
    ),
    bullets: s,
    recommendedTools: d,
    recommendedProducts: m,
    toolDirection: A(l.toolDirection ?? l.tool_direction, ""),
    disclaimer: A(l.disclaimer ?? l.toolDisclaimer ?? l.tool_disclaimer, ""),
    raw: e
  };
}
const Ci = {
  clientId: "no1",
  headline: "Chiptuning Händler werden",
  intro: "Finde heraus, welches Partner-Modell und welche Tool-Richtung zu dir passt.",
  privacyNotice: "Mit dem Absenden erklärst du dich einverstanden, dass deine Angaben zur Bearbeitung deiner Anfrage gespeichert werden.",
  ctaLabel: "Anfrage senden"
}, Bp = [
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
], Hp = [
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
], Qp = [
  { value: "pkw", title: "PKW", text: "Standard-Fahrzeuge, Performance und Daily Driver." },
  { value: "lkw", title: "LKW", text: "Nutzfahrzeuge, Flotten und Speditionen." },
  { value: "motorrad", title: "Motorrad", text: "Bike-Tuning und Performance-Anwendungen." },
  { value: "agrar", title: "Agrar", text: "Traktoren, Landmaschinen und Eco-Anwendungen." },
  { value: "atv", title: "Quad", text: "ATV, Quad und Side-by-Side." },
  { value: "boat", title: "Boot", text: "Boote und Marine-Anwendungen." },
  { value: "jetski", title: "Jetski", text: "JetSki und Wassersport-Fahrzeuge." }
], Qc = [
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
], Gp = [
  { value: "stage1", title: "Stage 1", text: "Leistungsoptimierung für Kundenfahrzeuge." },
  { value: "stage2", title: "Stage 2", text: "Optimierung mit Hardware-Anpassungen." },
  { value: "eco", title: "Eco-Tuning", text: "Verbrauchsoptimierung für Flotten und Gewerbe." },
  { value: "fileService", title: "File Service", text: "Files lesen, prüfen lassen und schreiben." },
  { value: "diagnostic", title: "Diagnose Support", text: "Technische Rückfragen, Fehlerbilder und Fälle." },
  { value: "special", title: "Sonderoptionen", text: "VMax, DSG, Start-Stop und weitere Optionen." }
], Gc = [
  {
    value: "full_package",
    title: "Website + Konfigurator",
    text: "Komplette Website mit Website-Modul und Chiptuning-Konfigurator."
  },
  {
    value: "website_only",
    title: "Nur Website",
    text: "Die Händler-Website ohne Konfigurator."
  },
  {
    value: "configurator_only",
    title: "Nur Chiptuning-Konfigurator",
    text: "Das Widget für die Fahrzeug- und Leistungsanfrage."
  },
  {
    value: "none",
    title: "Nicht benötigt",
    text: "Ich brauche dafür aktuell keine Website-Empfehlung."
  }
], Yp = [
  { value: "low", title: "1–5 Fahrzeuge / Monat", text: "Kleiner Start oder gelegentliche Anfragen." },
  { value: "medium", title: "6–20 Fahrzeuge / Monat", text: "Regelmäßiges Geschäft mit Wachstum." },
  { value: "high", title: "20+ Fahrzeuge / Monat", text: "Professioneller Betrieb mit Volumen." },
  { value: "network", title: "Händler / Netzwerk", text: "Mehrere Standorte, Slaves oder Partner." }
];
function Xp(e, t) {
  if (e.tool === "none")
    return {
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
    };
  if (e.website && e.website !== "none") {
    const r = {
      full_package: {
        packageName: "Händler-Website Komplettpaket",
        summary: "Für deinen Start als Chiptuning-Händler empfehlen wir dir eine komplette Website mit integriertem Chiptuning-Konfigurator. Damit können Kunden direkt Fahrzeuge auswählen, Leistungsdaten ansehen und Anfragen senden.",
        bullets: [
          "Website + Konfigurator im Paket",
          "Schneller Start mit kompletter Händlerpräsenz",
          "Direkte technische Abstimmung für die Einbindung"
        ],
        cta: "Website-Paket anfragen"
      },
      website_only: {
        packageName: "Händler-Website",
        summary: "Du brauchst zuerst einen professionellen Online-Auftritt. Die Website kann später jederzeit um den Chiptuning-Konfigurator erweitert werden.",
        bullets: [
          "Professionelle Händler-Website",
          "Struktur für Kontakt und Leistungspakete",
          "Geeignet für den reinen Webauftritt"
        ],
        cta: "Website anfragen"
      },
      configurator_only: {
        packageName: "Chiptuning-Konfigurator Widget",
        summary: "Du hast bereits eine Website. Der Konfigurator kann als Widget oder API-Anbindung integriert werden und erweitert deine Seite um Fahrzeugauswahl, Leistungsdaten und Anfragefunktion.",
        bullets: [
          "Konfigurator-Widget für deine Website",
          "Einbindung in bestehende Seiten",
          "Fokus auf Fahrzeug- und Leistungsanfragen"
        ],
        cta: "Konfigurator anfragen"
      }
    }[e.website];
    if (r)
      return {
        status: "ready",
        packageKey: e.website,
        packageName: r.packageName,
        leadStatus: "new_partner_lead",
        cta: r.cta,
        summary: r.summary,
        bullets: r.bullets
      };
  }
  return e.goal === "network" || e.volume === "network" ? {
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
const Zp = { low: 3, medium: 12, high: 25, network: 40 }, Jp = [
  {
    key: "entry",
    label: "Einstieg",
    type: "questions",
    questions: [
      { answerKey: "goal", title: "Was ist dein Ziel?", questionType: "generic", multiSelect: !1, options: Bp },
      { answerKey: "experience", title: "Wie viel Erfahrung hast du?", questionType: "generic", multiSelect: !1, options: Hp }
    ]
  },
  {
    key: "vehicleTool",
    label: "Fahrzeug & Tool",
    type: "questions",
    questions: [
      { answerKey: "vehicles", title: "Welche Fahrzeuge möchtest du bearbeiten?", questionType: "vehicleType", multiSelect: !0, options: Qp },
      {
        answerKey: "tool",
        title: "Hast du bereits ein eigenes Tool?",
        questionType: "toolManufacturer",
        multiSelect: !1,
        helpText: "Ohne eigenes Tuning-Tool ist keine direkte Händler-Aktivierung möglich. Dann geht es zuerst in die Tool-Beratung.",
        options: Qc
      }
    ]
  },
  {
    key: "offerVolume",
    label: "Angebot & Volumen",
    type: "questions",
    questions: [
      { answerKey: "services", title: "Welche Leistungen willst du anbieten?", questionType: "generic", multiSelect: !0, options: Gp },
      { answerKey: "website", title: "Welche Website möchtest du anbieten?", questionType: "generic", multiSelect: !1, options: Gc },
      {
        answerKey: "volume",
        title: "Wie viele Fahrzeuge erwartest du?",
        questionType: "volume",
        multiSelect: !1,
        options: Yp.map((e) => ({ ...e, numericValue: Zp[e.value] }))
      }
    ]
  },
  { key: "recommendation", label: "Empfehlung", type: "recommendation", questions: [] },
  {
    key: "contact",
    label: "Kontakt",
    type: "contact",
    questions: [
      { answerKey: "name", title: "Name", questionType: "generic", multiSelect: !1, fieldType: "text", required: !0, options: [] },
      { answerKey: "company", title: "Firma", questionType: "generic", multiSelect: !1, fieldType: "text", required: !1, options: [] },
      { answerKey: "email", title: "E-Mail", questionType: "generic", multiSelect: !1, fieldType: "email", required: !0, options: [] },
      { answerKey: "phone", title: "Telefon", questionType: "generic", multiSelect: !1, fieldType: "text", required: !1, options: [] },
      { answerKey: "message", title: "Nachricht", questionType: "generic", multiSelect: !1, fieldType: "textarea", required: !1, options: [] },
      {
        answerKey: "privacyAccepted",
        title: "Mit dem Absenden erklärst du dich einverstanden, dass deine Angaben zur Bearbeitung der Anfrage gespeichert werden und wir dich kontaktieren.",
        questionType: "generic",
        multiSelect: !1,
        fieldType: "checkbox",
        required: !0,
        options: []
      }
    ]
  }
];
function qp(e) {
  return {
    value: e.value,
    title: e.title,
    text: e.text ?? "",
    icon: e.icon,
    imageUrl: e.imageUrl,
    id: e.id,
    numericValue: e.numericValue
  };
}
function pl(e, t) {
  return e[t];
}
function Ts(e, t, n) {
  return { ...e, [t]: n };
}
function er(e, t) {
  var n;
  return t === "privacyAccepted" ? e.privacyAccepted : (n = e.contact) == null ? void 0 : n[t];
}
function br(e, t) {
  const n = e.dependsOnAnswerKey, r = e.dependsOnValue;
  if (!n || !r) return !0;
  const l = pl(t, n) ?? er(t, n);
  return Array.isArray(l) ? l.includes(r) : String(l ?? "") === r;
}
const bp = [
  "Fahrzeugdatenbank API für 1 Jahr mit drin"
];
function ji(e, t) {
  const n = e ?? [];
  return n.includes(t) ? n.filter((r) => r !== t) : [...n, t];
}
function em(e) {
  var t;
  return !!((t = e.vehicles) != null && t.length && e.tool);
}
function _e(e) {
  if (!e) return;
  const t = Number(e);
  if (Number.isFinite(t))
    return `${t.toLocaleString("de-DE", { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €*`;
}
function Ht(e) {
  const t = Number(e == null ? void 0 : e.specialPrice);
  return Number.isFinite(t) && t > 0;
}
function kt(e) {
  return Ht(e) ? e == null ? void 0 : e.specialPrice : e == null ? void 0 : e.price;
}
function tm(e, t) {
  if (!e) return 0;
  const n = t.find((r) => r.value === e);
  return (n == null ? void 0 : n.numericValue) ?? 0;
}
function nm(e) {
  return !e || e === "none" || e === "other" ? "unknown" : "slave";
}
function Ls(e, t, n, r, l) {
  const i = t.contact ?? {}, o = t.vehicles ?? [], u = o.map((S) => n[S]).filter((S) => typeof S == "number"), s = t.services ?? [], d = t.website ?? "", m = t.tool ?? "", g = tm(t.volume, r), h = nm(t.tool);
  return {
    clientId: e,
    contact: i,
    answers: t,
    recommendation: l,
    source: "no1_partner_wizard",
    selectedTools: t.selectedTools ?? [],
    // New Symfony recommendation service fields (VehicleType IDs, not slugs).
    currentTool: m,
    vehicleCategories: u,
    mainVehicleCategory: u[0] ?? "",
    services: s,
    website: d,
    goal: t.goal ?? "",
    experience: t.experience ?? "",
    monthlyVehicleCount: g,
    desiredToolLevel: h,
    // Lead / compatibility fields.
    packageName: l == null ? void 0 : l.packageName,
    packageKey: l == null ? void 0 : l.packageKey,
    leadStatus: l == null ? void 0 : l.leadStatus,
    status: l == null ? void 0 : l.status,
    name: i.name,
    company: i.company,
    email: i.email,
    phone: i.phone,
    message: i.message,
    privacyAccepted: t.privacyAccepted === !0,
    vehicles: o,
    tool: m,
    volume: t.volume,
    payload: {
      clientId: e,
      contact: i,
      answers: t,
      recommendation: l,
      currentTool: m,
      vehicleCategories: u,
      mainVehicleCategory: u[0] ?? "",
      services: s,
      website: d,
      monthlyVehicleCount: g,
      desiredToolLevel: h,
      privacyAccepted: t.privacyAccepted === !0,
      source: "no1_partner_wizard"
    }
  };
}
function el() {
  return /* @__PURE__ */ a.jsxs("svg", { viewBox: "0 0 20 20", fill: "none", xmlns: "http://www.w3.org/2000/svg", "aria-hidden": "true", children: [
    /* @__PURE__ */ a.jsx("circle", { cx: "10", cy: "10", r: "9", fill: "currentColor", opacity: "0.15" }),
    /* @__PURE__ */ a.jsx("path", { d: "M6 10.5L8.5 13L14 7", stroke: "currentColor", strokeWidth: "2", strokeLinecap: "round", strokeLinejoin: "round" })
  ] });
}
function rm() {
  return /* @__PURE__ */ a.jsxs("svg", { viewBox: "0 0 20 20", fill: "none", xmlns: "http://www.w3.org/2000/svg", "aria-hidden": "true", children: [
    /* @__PURE__ */ a.jsx("circle", { cx: "10", cy: "10", r: "9", fill: "currentColor", opacity: "0.15" }),
    /* @__PURE__ */ a.jsx("path", { d: "M7 7L13 13M13 7L7 13", stroke: "currentColor", strokeWidth: "2", strokeLinecap: "round", strokeLinejoin: "round" })
  ] });
}
function lm({
  option: e,
  selected: t,
  onClick: n
}) {
  const r = e.imageUrl ?? e.icon;
  return /* @__PURE__ */ a.jsxs(
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
        r && /* @__PURE__ */ a.jsx("img", { className: "tfdWizard__optionIcon", src: r, alt: "", loading: "lazy", "aria-hidden": "true" }),
        /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__optionBody", children: [
          /* @__PURE__ */ a.jsx("strong", { children: e.title }),
          /* @__PURE__ */ a.jsx("span", { children: e.text })
        ] })
      ]
    }
  );
}
function im({
  clientId: e = "no1",
  apiBaseUrl: t = "",
  primaryColor: n = "#E31E24",
  theme: r = "light"
}) {
  const [l, i] = ee.useState(Ci), [o, u] = ee.useState(""), [s, d] = ee.useState(0), [m, g] = ee.useState({}), [h, S] = ee.useState(!1), [z, _] = ee.useState(""), [B, f] = ee.useState(null), [c, p] = ee.useState(""), [y, C] = ee.useState(!1), [N, P] = ee.useState(null);
  ee.useEffect(() => {
    let v = !0;
    return Rp(t, e).then((x) => {
      v && (i({ ...Ci, ...x }), u(""));
    }).catch((x) => {
      if (!v) return;
      i(Ci);
      const W = typeof x == "object" && x !== null && "status" in x ? String(x.status ?? "offline") : "offline";
      u(`Config konnte nicht geladen werden (${W}). Lokale Fallback-Konfiguration aktiv.`);
    }), () => {
      v = !1;
    };
  }, [t, e]);
  const T = ee.useMemo(
    () => Xp(m),
    [m, l]
  ), K = ee.useMemo(
    () => l.steps && l.steps.length > 0 ? l.steps : Jp,
    [l]
  ), O = ee.useMemo(() => {
    const v = {};
    for (const x of K)
      for (const W of x.questions) {
        const U = W.options.map(qp);
        if (W.answerKey === "tool") {
          const Q = U.some((L) => L.value === "none");
          v[W.answerKey] = Q ? U : [Qc[0], ...U];
        } else if (W.answerKey === "website") {
          const Q = U.some((L) => L.value === "none");
          v[W.answerKey] = Q ? U : [Gc[0], ...U];
        } else
          v[W.answerKey] = U;
      }
    return v;
  }, [K]), ue = O.vehicles ?? [], Ut = O.services ?? [], $t = O.website ?? [], Wr = O.tool ?? [], Fr = O.volume ?? [], Vt = ee.useMemo(
    () => Object.fromEntries(
      ue.filter((v) => typeof v.id == "number").map((v) => [v.value, v.id])
    ),
    [ue]
  );
  ee.useEffect(() => {
    if (!em(m)) {
      f(null), p("");
      return;
    }
    let v = !0;
    C(!0), p("");
    const x = Ls(e, m, Vt, Fr, T);
    return Dp(t, x).then((W) => {
      v && (f(W), p(""));
    }).catch((W) => {
      if (!v) return;
      f(null);
      const U = W instanceof Error ? W.message : "Recommendation API nicht erreichbar.";
      p(`${U} Lokale Empfehlung aktiv.`);
    }).finally(() => {
      v && C(!1);
    }), () => {
      v = !1;
    };
  }, [m, t, e, T, Vt]);
  const se = K[s], k = B ?? T, M = ee.useMemo(
    () => (k.recommendedTools ?? []).map((v) => v.key),
    [k]
  ), R = Math.round((s + 1) / K.length * 100);
  ee.useEffect(() => {
    if (!N) return;
    const v = (x) => {
      x.key === "Escape" && P(null);
    };
    return window.addEventListener("keydown", v), () => window.removeEventListener("keydown", v);
  }, [N]), ee.useEffect(() => {
    g((v) => {
      const x = v.selectedTools ?? [];
      if (x.length === 0) return v;
      const W = x.filter((U) => M.includes(U));
      return W.length === x.length ? v : { ...v, selectedTools: W };
    });
  }, [M]);
  const H = ee.useMemo(() => se.type === "recommendation" ? !0 : se.type === "contact" ? se.questions.filter((v) => br(v, m) && v.required).every((v) => {
    const x = er(m, v.answerKey);
    return v.fieldType === "checkbox" ? v.multiSelect ? Array.isArray(x) && x.length > 0 : x === !0 : !!(x && String(x).trim());
  }) : se.questions.filter((v) => br(v, m)).every((v) => {
    const x = pl(m, v.answerKey);
    return v.multiSelect ? Array.isArray(x) && x.length > 0 : !!x;
  }), [m, se]), re = (v, x) => {
    g((W) => Ts(W, v, x)), _("");
  }, ln = (v, x) => {
    g((W) => Ts(W, v, ji(pl(W, v), x))), _("");
  }, be = ue.length > 0 && ue.every((v) => (m.vehicles ?? []).includes(v.value)), An = () => {
    g((v) => ({
      ...v,
      vehicles: be ? [] : ue.map((x) => x.value)
    })), _("");
  }, ot = (v, x, W = !1) => /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__grid", children: v.map((U) => {
    const Q = pl(m, x), L = Array.isArray(Q) ? Q.includes(U.value) : Q === U.value;
    return /* @__PURE__ */ a.jsx(
      lm,
      {
        option: U,
        selected: L,
        onClick: () => {
          W ? ln(x, U.value) : re(x, U.value);
        }
      },
      U.value
    );
  }) }), on = () => {
    H && (d((v) => Math.min(v + 1, K.length - 1)), _(""));
  }, Yc = () => {
    d((v) => Math.max(v - 1, 0)), _("");
  }, Mr = (v, x) => {
    g((W) => ({
      ...W,
      contact: {
        ...W.contact,
        [v]: x
      }
    })), _("");
  }, Xc = (v, x) => {
    g(v === "privacyAccepted" ? (W) => ({ ...W, privacyAccepted: x }) : (W) => ({
      ...W,
      contact: {
        ...W.contact,
        [v]: x ? "true" : ""
      }
    })), _("");
  }, Zc = (v, x) => {
    g((W) => {
      var L;
      const U = (L = W.contact) == null ? void 0 : L[v], Q = Array.isArray(U) ? U : [];
      return {
        ...W,
        contact: {
          ...W.contact,
          [v]: ji(Q, x)
        }
      };
    }), _("");
  }, ku = (v) => {
    g((x) => ({
      ...x,
      selectedTools: ji(x.selectedTools, v)
    })), _("");
  }, Or = (v) => {
    P(v);
  }, Jc = () => {
    if (!N) return null;
    const v = N.product, x = _e(kt(v)), W = Ht(v) ? _e(v == null ? void 0 : v.price) : void 0, U = N.imageUrl ?? (v == null ? void 0 : v.imageUrl), Q = (m.selectedTools ?? []).includes(N.key);
    return /* @__PURE__ */ a.jsx(
      "div",
      {
        className: "tfdWizard__modalBackdrop",
        role: "presentation",
        onClick: () => P(null),
        children: /* @__PURE__ */ a.jsxs(
          "div",
          {
            className: "tfdWizard__productModal",
            role: "dialog",
            "aria-modal": "true",
            "aria-label": (v == null ? void 0 : v.title) ?? N.name,
            onClick: (L) => L.stopPropagation(),
            children: [
              /* @__PURE__ */ a.jsx(
                "button",
                {
                  type: "button",
                  className: "tfdWizard__modalClose",
                  onClick: () => P(null),
                  "aria-label": "Produktansicht schließen",
                  children: "×"
                }
              ),
              /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__productModalMedia", children: U ? /* @__PURE__ */ a.jsx("img", { src: U, alt: (v == null ? void 0 : v.title) ?? N.name }) : /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__productModalPlaceholder", children: "No1" }) }),
              /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__productModalBody", children: [
                /* @__PURE__ */ a.jsx("span", { children: "Produktdetails" }),
                /* @__PURE__ */ a.jsx("h3", { children: (v == null ? void 0 : v.title) ?? N.name }),
                x && /* @__PURE__ */ a.jsxs("p", { children: [
                  W && /* @__PURE__ */ a.jsx("s", { className: "tfdWizard__priceOriginal", children: W }),
                  /* @__PURE__ */ a.jsx("strong", { children: x })
                ] }),
                N.description && /* @__PURE__ */ a.jsx("p", { children: N.description }),
                /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__productFacts", children: [
                  (v == null ? void 0 : v.sku) && /* @__PURE__ */ a.jsxs("div", { children: [
                    /* @__PURE__ */ a.jsx("em", { children: "SKU" }),
                    /* @__PURE__ */ a.jsx("b", { children: v.sku })
                  ] }),
                  N.level && /* @__PURE__ */ a.jsxs("div", { children: [
                    /* @__PURE__ */ a.jsx("em", { children: "Tool-Level" }),
                    /* @__PURE__ */ a.jsx("b", { children: N.level })
                  ] }),
                  /* @__PURE__ */ a.jsxs("div", { children: [
                    /* @__PURE__ */ a.jsx("em", { children: "Empfehlung" }),
                    /* @__PURE__ */ a.jsx("b", { children: N.fromCombination ? "Exakt passend" : "Passend" })
                  ] })
                ] }),
                /* @__PURE__ */ a.jsx(
                  "button",
                  {
                    type: "button",
                    className: "tfdWizard__modalSelect",
                    onClick: () => ku(N.key),
                    "aria-pressed": Q,
                    children: Q ? "Aus Auswahl entfernen" : "Zur Auswahl hinzufügen"
                  }
                )
              ] })
            ]
          }
        )
      }
    );
  }, qc = async () => {
    if (!H || h) return;
    S(!0), _("");
    const v = Ls(e, m, Vt, Fr, k);
    try {
      await Ip(t, v), _("Anfrage wurde gespeichert. Wir melden uns mit der passenden Empfehlung.");
    } catch (x) {
      const W = x instanceof Error ? x.message : "Unbekannter API-Fehler";
      _(`Anfrage konnte nicht gesendet werden: ${W}`);
    } finally {
      S(!1);
    }
  }, bc = () => {
    var Re, vt;
    const v = (m.vehicles ?? []).map((E) => ue.find((D) => D.value === E)).filter((E) => !!E).slice(0, 4), x = (m.services ?? []).map((E) => Ut.find((D) => D.value === E)).filter((E) => !!E).slice(0, 4), W = $t.find((E) => E.value === m.website), U = (Re = Wr.find((E) => E.value === m.tool)) == null ? void 0 : Re.title, Q = (k.recommendedTools ?? []).filter((E) => (m.selectedTools ?? []).includes(E.key)), L = Q.reduce((E, D) => {
      const He = Number(kt(D.product));
      return Number.isFinite(He) ? E + He : E;
    }, 0), Be = !!((vt = m.vehicles) != null && vt.length && m.tool), F = !!(m.tool && m.tool !== "none"), Ae = Q.length > 0, je = se.type === "recommendation" && ["full_package", "website_only", "configurator_only"].includes(k.packageKey ?? "");
    return /* @__PURE__ */ a.jsxs("aside", { className: "tfdWizard__promoAside", "aria-label": "Tool Empfehlungen", children: [
      (v.length > 0 || x.length > 0 || W || U) && /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__promoCard tfdWizard__summaryCard", children: [
        /* @__PURE__ */ a.jsx("span", { children: "Deine Angaben" }),
        U && /* @__PURE__ */ a.jsxs("p", { className: "tfdWizard__summaryLine", children: [
          /* @__PURE__ */ a.jsx("b", { children: "Tool:" }),
          /* @__PURE__ */ a.jsx("span", { className: `tfdWizard__statusIcon ${F ? "tfdWizard__statusIcon--yes" : "tfdWizard__statusIcon--no"}`, children: F ? /* @__PURE__ */ a.jsx(el, {}) : /* @__PURE__ */ a.jsx(rm, {}) }),
          U
        ] }),
        v.length > 0 && /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__summaryLine", children: [
          /* @__PURE__ */ a.jsx("b", { children: "Fahrzeuge:" }),
          /* @__PURE__ */ a.jsx("span", { className: "tfdWizard__summaryIconList", children: v.map((E) => {
            const D = E.imageUrl ?? E.icon;
            return /* @__PURE__ */ a.jsxs("span", { className: "tfdWizard__summaryIconItem", title: E.title, children: [
              D ? /* @__PURE__ */ a.jsx("img", { src: D, alt: "", loading: "lazy" }) : /* @__PURE__ */ a.jsx("span", { className: "tfdWizard__statusIcon tfdWizard__statusIcon--yes", children: /* @__PURE__ */ a.jsx(el, {}) }),
              E.title
            ] }, E.value);
          }) })
        ] }),
        x.length > 0 && /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__summaryLine", children: [
          /* @__PURE__ */ a.jsx("b", { children: "Leistungen:" }),
          /* @__PURE__ */ a.jsx("span", { className: "tfdWizard__summaryIconList", children: x.map((E) => {
            const D = E.imageUrl ?? E.icon;
            return /* @__PURE__ */ a.jsxs("span", { className: "tfdWizard__summaryIconItem", title: E.title, children: [
              D ? /* @__PURE__ */ a.jsx("img", { src: D, alt: "", loading: "lazy" }) : /* @__PURE__ */ a.jsx("span", { className: "tfdWizard__statusIcon tfdWizard__statusIcon--yes", children: /* @__PURE__ */ a.jsx(el, {}) }),
              E.title
            ] }, E.value);
          }) })
        ] }),
        W && /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__summaryLine", children: [
          /* @__PURE__ */ a.jsx("b", { children: "Website:" }),
          /* @__PURE__ */ a.jsx("span", { className: "tfdWizard__summaryIconList", children: /* @__PURE__ */ a.jsxs("span", { className: "tfdWizard__summaryIconItem", title: W.title, children: [
            /* @__PURE__ */ a.jsx("span", { className: "tfdWizard__statusIcon tfdWizard__statusIcon--yes", children: /* @__PURE__ */ a.jsx(el, {}) }),
            W.title
          ] }) })
        ] })
      ] }),
      se.type === "recommendation" && je && /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__promoCard tfdWizard__promoCard--accent", children: [
        /* @__PURE__ */ a.jsx("span", { children: "Website-Empfehlung" }),
        /* @__PURE__ */ a.jsx("strong", { children: k.packageName }),
        /* @__PURE__ */ a.jsx("p", { children: k.packageText ?? k.summary })
      ] }),
      se.type === "recommendation" && /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__promoCard tfdWizard__selectedProductsCard", children: [
        /* @__PURE__ */ a.jsx("span", { children: "Ausgewählte Produkte" }),
        Ae ? /* @__PURE__ */ a.jsxs(a.Fragment, { children: [
          /* @__PURE__ */ a.jsxs("strong", { children: [
            Q.length,
            " Position",
            Q.length === 1 ? "" : "en"
          ] }),
          /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__selectedProductsList", children: Q.map((E) => {
            var yt, Rn, Dn, In, Un;
            const D = _e(kt(E.product)), He = Ht(E.product) ? _e((yt = E.product) == null ? void 0 : yt.price) : void 0;
            return /* @__PURE__ */ a.jsxs("article", { children: [
              (E.imageUrl || ((Rn = E.product) == null ? void 0 : Rn.imageUrl)) && /* @__PURE__ */ a.jsx("img", { src: E.imageUrl ?? ((Dn = E.product) == null ? void 0 : Dn.imageUrl), alt: ((In = E.product) == null ? void 0 : In.title) ?? E.name, loading: "lazy" }),
              /* @__PURE__ */ a.jsxs("div", { children: [
                /* @__PURE__ */ a.jsx("b", { children: ((Un = E.product) == null ? void 0 : Un.title) ?? E.name }),
                He && /* @__PURE__ */ a.jsx("s", { className: "tfdWizard__priceOriginal", children: He }),
                D && /* @__PURE__ */ a.jsx("em", { children: D }),
                /* @__PURE__ */ a.jsx("button", { type: "button", onClick: () => Or(E), children: "Details" })
              ] })
            ] }, E.key);
          }) }),
          L > 0 && /* @__PURE__ */ a.jsxs("p", { className: "tfdWizard__selectedProductsTotal", children: [
            /* @__PURE__ */ a.jsx("b", { children: "Summe:" }),
            " ",
            _e(String(L))
          ] }),
          /* @__PURE__ */ a.jsx("p", { className: "tfdWizard__priceDisclaimer", children: "* Preise netto, zzgl. gesetzl. MwSt." })
        ] }) : /* @__PURE__ */ a.jsx("p", { children: "Wähle ein oder mehrere passende Tools aus. Die Auswahl wird mit deiner Anfrage übergeben." })
      ] }),
      !F && s < 3 && /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__promoCard tfdWizard__promoCard--accent", children: [
        /* @__PURE__ */ a.jsx("span", { children: "Tool Werbung" }),
        /* @__PURE__ */ a.jsx("strong", { children: "Passendes Leseschreibtool" }),
        /* @__PURE__ */ a.jsx("p", { children: "Für den Händlerstart brauchst du ein unterstütztes Tool. Wir prüfen Protokolle und Fahrzeugbereiche." }),
        Be && c && /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__miniNotice", children: c }),
        Be ? y ? /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__miniNotice", children: "Empfehlung wird aus dem Backend geladen..." }) : k.recommendedTools && k.recommendedTools.length > 0 ? /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__adList", children: k.recommendedTools.slice(0, 4).map((E) => {
          var yt;
          const D = _e(kt(E.product)), He = Ht(E.product) ? _e((yt = E.product) == null ? void 0 : yt.price) : void 0;
          return /* @__PURE__ */ a.jsxs("article", { className: E.imageUrl ? "tfdWizard__adItem--withImage" : void 0, children: [
            E.imageUrl && /* @__PURE__ */ a.jsx("img", { className: "tfdWizard__adImage", src: E.imageUrl, alt: E.name, loading: "lazy" }),
            /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__adBody", children: [
              /* @__PURE__ */ a.jsx("b", { children: E.name }),
              E.fromCombination && /* @__PURE__ */ a.jsx("span", { className: "tfdWizard__adBadge", children: "Passt zu deiner Auswahl" }),
              E.description && /* @__PURE__ */ a.jsx("small", { children: E.description }),
              E.product && /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__adProduct", children: [
                He && /* @__PURE__ */ a.jsx("s", { className: "tfdWizard__priceOriginal", children: He }),
                D && /* @__PURE__ */ a.jsx("span", { className: "tfdWizard__adPrice", children: D }),
                /* @__PURE__ */ a.jsx(
                  "button",
                  {
                    type: "button",
                    className: "tfdWizard__adLink",
                    onClick: () => Or(E),
                    children: "Produkt ansehen"
                  }
                )
              ] })
            ] })
          ] }, E.key);
        }) }) : Be ? /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__miniNotice", children: "Für diese Fahrzeugauswahl ist noch keine passende Tool-Empfehlung hinterlegt." }) : /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__tagList", children: [
          /* @__PURE__ */ a.jsx("em", { children: "Autotuner" }),
          /* @__PURE__ */ a.jsx("em", { children: "KESS3" }),
          /* @__PURE__ */ a.jsx("em", { children: "FLEX" })
        ] }) : /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__miniNotice", children: "Beantworte Fahrzeuge und Tool-Status, um passende Tools zu sehen." }),
        k.recommendedTools && k.recommendedTools.length > 0 && /* @__PURE__ */ a.jsx("p", { className: "tfdWizard__priceDisclaimer", children: "* Preise netto, zzgl. gesetzl. MwSt." })
      ] }),
      l.toolDisclaimer && se.key === "vehicleTool" && /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__miniNotice", children: l.toolDisclaimer }),
      se.type === "contact" && /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__promoCard tfdWizard__selectedProductsCard", children: [
        /* @__PURE__ */ a.jsx("span", { children: "Ausgewählte Produkte" }),
        Ae ? /* @__PURE__ */ a.jsxs(a.Fragment, { children: [
          /* @__PURE__ */ a.jsxs("strong", { children: [
            Q.length,
            " Position",
            Q.length === 1 ? "" : "en"
          ] }),
          /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__selectedProductsList", children: Q.map((E) => {
            var yt, Rn, Dn, In, Un;
            const D = _e(kt(E.product)), He = Ht(E.product) ? _e((yt = E.product) == null ? void 0 : yt.price) : void 0;
            return /* @__PURE__ */ a.jsxs("article", { children: [
              (E.imageUrl || ((Rn = E.product) == null ? void 0 : Rn.imageUrl)) && /* @__PURE__ */ a.jsx("img", { src: E.imageUrl ?? ((Dn = E.product) == null ? void 0 : Dn.imageUrl), alt: ((In = E.product) == null ? void 0 : In.title) ?? E.name, loading: "lazy" }),
              /* @__PURE__ */ a.jsxs("div", { children: [
                /* @__PURE__ */ a.jsx("b", { children: ((Un = E.product) == null ? void 0 : Un.title) ?? E.name }),
                He && /* @__PURE__ */ a.jsx("s", { className: "tfdWizard__priceOriginal", children: He }),
                D && /* @__PURE__ */ a.jsx("em", { children: D }),
                /* @__PURE__ */ a.jsx("button", { type: "button", onClick: () => Or(E), children: "Details" })
              ] })
            ] }, E.key);
          }) }),
          L > 0 && /* @__PURE__ */ a.jsxs("p", { className: "tfdWizard__selectedProductsTotal", children: [
            /* @__PURE__ */ a.jsx("b", { children: "Summe:" }),
            " ",
            _e(String(L))
          ] }),
          /* @__PURE__ */ a.jsx("p", { className: "tfdWizard__priceDisclaimer", children: "* Preise netto, zzgl. gesetzl. MwSt." })
        ] }) : /* @__PURE__ */ a.jsx("p", { children: "Keine Produkte ausgewählt. Du kannst im vorherigen Schritt passende Tools auswählen." })
      ] })
    ] });
  }, ed = (v) => /* @__PURE__ */ a.jsx(a.Fragment, { children: v.questions.filter((x) => br(x, m)).map((x) => /* @__PURE__ */ a.jsxs("section", { className: "tfdWizard__block", children: [
    /* @__PURE__ */ a.jsx("h3", { className: "tfdWizard__title", children: x.title }),
    x.questionType === "vehicleType" && /* @__PURE__ */ a.jsx(
      "button",
      {
        type: "button",
        className: "tfdWizard__selectAllButton",
        onClick: An,
        "data-testid": "vehicles-select-all-button",
        children: be ? "Alle abwählen" : "Alle auswählen"
      }
    ),
    x.helpText && /* @__PURE__ */ a.jsx("p", { className: "tfdWizard__lead", children: x.helpText }),
    ot(O[x.answerKey] ?? [], x.answerKey, x.multiSelect)
  ] }, x.answerKey)) }), td = (v) => {
    const x = (L) => L.fieldType === "checkbox" && !L.multiSelect, W = v.questions.filter((L) => br(L, m)), U = W.filter((L) => !x(L)), Q = W.filter(x);
    return /* @__PURE__ */ a.jsxs("section", { className: "tfdWizard__block", children: [
      /* @__PURE__ */ a.jsx("h3", { className: "tfdWizard__title", children: "Kontakt aufnehmen" }),
      /* @__PURE__ */ a.jsx("p", { className: "tfdWizard__lead", children: "Sende uns deine Angaben. Wir prüfen dein Partner-Modell und melden uns mit der passenden Empfehlung." }),
      /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__form", children: U.map((L) => {
        const Be = er(m, L.answerKey), F = typeof Be == "string" ? Be : "", Ae = Array.isArray(Be) ? Be : [], je = `${L.title}${L.required ? " *" : ""}`, Re = `contact-${L.answerKey}-input`, vt = L.fieldType === "radio" || L.fieldType === "checkbox", E = L.fieldType === "textarea" || vt;
        return vt ? /* @__PURE__ */ a.jsxs("div", { className: E ? "tfdWizard__fieldFull" : void 0, children: [
          /* @__PURE__ */ a.jsx("span", { className: "tfdWizard__checkboxGroupLabel", children: je }),
          L.fieldType === "radio" ? /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__radioGroup", "data-testid": Re, children: L.options.map((D) => /* @__PURE__ */ a.jsxs("label", { className: "tfdWizard__radioOption", children: [
            /* @__PURE__ */ a.jsx(
              "input",
              {
                type: "radio",
                name: L.answerKey,
                checked: F === D.value,
                onChange: () => Mr(L.answerKey, D.value)
              }
            ),
            /* @__PURE__ */ a.jsx("span", { children: D.title })
          ] }, D.value)) }) : /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__checkboxGroup", "data-testid": Re, children: L.options.map((D) => /* @__PURE__ */ a.jsxs("label", { className: "tfdWizard__checkboxOption", children: [
            /* @__PURE__ */ a.jsx(
              "input",
              {
                type: "checkbox",
                checked: Ae.includes(D.value),
                onChange: () => Zc(L.answerKey, D.value)
              }
            ),
            /* @__PURE__ */ a.jsx("span", { children: D.title })
          ] }, D.value)) })
        ] }, L.answerKey) : /* @__PURE__ */ a.jsxs("label", { className: E ? "tfdWizard__fieldFull" : void 0, children: [
          /* @__PURE__ */ a.jsx("span", { children: je }),
          L.fieldType === "textarea" ? /* @__PURE__ */ a.jsx(
            "textarea",
            {
              value: F,
              onChange: (D) => Mr(L.answerKey, D.target.value),
              "data-testid": Re
            }
          ) : L.fieldType === "select" ? /* @__PURE__ */ a.jsxs(
            "select",
            {
              value: F,
              onChange: (D) => Mr(L.answerKey, D.target.value),
              "data-testid": Re,
              children: [
                /* @__PURE__ */ a.jsx("option", { value: "", children: "Bitte wählen" }),
                L.options.map((D) => /* @__PURE__ */ a.jsx("option", { value: D.value, children: D.title }, D.value))
              ]
            }
          ) : /* @__PURE__ */ a.jsx(
            "input",
            {
              type: L.fieldType === "email" ? "email" : "text",
              value: F,
              onChange: (D) => Mr(L.answerKey, D.target.value),
              "data-testid": Re
            }
          )
        ] }, L.answerKey);
      }) }),
      Q.map((L) => {
        const Be = er(m, L.answerKey) === !0 || er(m, L.answerKey) === "true";
        return /* @__PURE__ */ a.jsxs("label", { className: "tfdWizard__privacy", children: [
          /* @__PURE__ */ a.jsx(
            "input",
            {
              type: "checkbox",
              checked: Be,
              onChange: (F) => Xc(L.answerKey, F.target.checked),
              "data-testid": `contact-${L.answerKey}-checkbox`
            }
          ),
          /* @__PURE__ */ a.jsx("span", { children: L.title })
        ] }, L.answerKey);
      }),
      z && /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__notice", children: z })
    ] });
  }, nd = () => {
    var v, x;
    switch (se.type) {
      case "questions":
        return ed(se);
      case "recommendation": {
        const W = Array.from(new Set([...k.bullets ?? [], ...bp].filter(Boolean))), U = ((v = k.recommendedTools) == null ? void 0 : v.slice(0, 4)) ?? [], Q = m.selectedTools ?? [];
        return U.filter((F) => Q.includes(F.key)).reduce((F, Ae) => {
          const je = Number(kt(Ae.product));
          return Number.isFinite(je) ? F + je : F;
        }, 0), (/* @__PURE__ */ new Set(["full_package", "website_only", "configurator_only"])).has(k.packageKey ?? ""), /* @__PURE__ */ a.jsxs("section", { className: "tfdWizard__block tfdWizard__recommendationStep", children: [
          /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__recommendationHero", children: [
            /* @__PURE__ */ a.jsx("span", { children: "Deine Empfehlung" }),
            /* @__PURE__ */ a.jsx("h3", { children: k.packageName }),
            /* @__PURE__ */ a.jsx("p", { children: k.summary })
          ] }),
          /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__recommendationPanel", children: [
            /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__recommendationPanelHeader", children: [
              /* @__PURE__ */ a.jsx("span", { children: "Passende Tools" }),
              y && /* @__PURE__ */ a.jsx("em", { children: "Wird geladen..." })
            ] }),
            U.length > 0 ? /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__toolShowcase", children: U.map((F) => {
              var vt;
              const Ae = _e(kt(F.product)), je = Ht(F.product) ? _e((vt = F.product) == null ? void 0 : vt.price) : void 0, Re = Q.includes(F.key);
              return /* @__PURE__ */ a.jsxs(
                "article",
                {
                  className: [
                    "tfdWizard__toolShowcaseItem",
                    F.imageUrl ? "tfdWizard__toolShowcaseItem--withImage" : "",
                    Re ? "tfdWizard__toolShowcaseItem--selected" : ""
                  ].filter(Boolean).join(" "),
                  children: [
                    F.imageUrl && /* @__PURE__ */ a.jsx("img", { className: "tfdWizard__toolShowcaseImage", src: F.imageUrl, alt: F.name, loading: "lazy" }),
                    /* @__PURE__ */ a.jsxs("div", { children: [
                      /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__toolShowcaseTop", children: [
                        /* @__PURE__ */ a.jsx("b", { children: F.name }),
                        /* @__PURE__ */ a.jsx(
                          "button",
                          {
                            type: "button",
                            className: "tfdWizard__cartToggle",
                            onClick: () => ku(F.key),
                            "aria-pressed": Re,
                            children: Re ? "Ausgewählt" : "Auswählen"
                          }
                        )
                      ] }),
                      F.fromCombination && /* @__PURE__ */ a.jsx("small", { children: "Passt exakt zu deiner Auswahl" }),
                      F.description && /* @__PURE__ */ a.jsx("p", { children: F.description }),
                      /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__toolShowcaseMeta", children: [
                        F.level && /* @__PURE__ */ a.jsx("span", { children: F.level }),
                        je && /* @__PURE__ */ a.jsx("s", { className: "tfdWizard__priceOriginal", children: je }),
                        Ae && /* @__PURE__ */ a.jsx("strong", { children: Ae }),
                        F.product && /* @__PURE__ */ a.jsx("button", { type: "button", onClick: () => Or(F), children: "Produkt ansehen" })
                      ] })
                    ] })
                  ]
                },
                F.key
              );
            }) }) : /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__miniNotice", children: "Für diese Auswahl ist noch keine passende Tool-Empfehlung hinterlegt." })
          ] }),
          k.recommendedProducts && k.recommendedProducts.length > 0 && /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__recommendationPanel", children: [
            /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__recommendationPanelHeader", children: /* @__PURE__ */ a.jsx("span", { children: "Produktempfehlungen" }) }),
            /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__toolShowcase", children: k.recommendedProducts.map((F) => {
              const Ae = _e(kt(F)), je = Ht(F) ? _e(F.price) : void 0;
              return /* @__PURE__ */ a.jsxs(
                "article",
                {
                  className: [
                    "tfdWizard__toolShowcaseItem",
                    F.imageUrl ? "tfdWizard__toolShowcaseItem--withImage" : ""
                  ].filter(Boolean).join(" "),
                  children: [
                    F.imageUrl && /* @__PURE__ */ a.jsx("img", { className: "tfdWizard__toolShowcaseImage", src: F.imageUrl, alt: F.title, loading: "lazy" }),
                    /* @__PURE__ */ a.jsxs("div", { children: [
                      /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__toolShowcaseTop", children: /* @__PURE__ */ a.jsx("b", { children: F.title }) }),
                      /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__toolShowcaseMeta", children: [
                        F.sku && /* @__PURE__ */ a.jsx("span", { children: F.sku }),
                        je && /* @__PURE__ */ a.jsx("s", { className: "tfdWizard__priceOriginal", children: je }),
                        Ae && /* @__PURE__ */ a.jsx("strong", { children: Ae })
                      ] })
                    ] })
                  ]
                },
                F.uid ?? F.title
              );
            }) })
          ] }),
          (U.length > 0 || (((x = k.recommendedProducts) == null ? void 0 : x.length) ?? 0) > 0) && /* @__PURE__ */ a.jsx("p", { className: "tfdWizard__priceDisclaimer", children: "* Preise netto, zzgl. gesetzl. MwSt." }),
          /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__extrasPanel", children: [
            /* @__PURE__ */ a.jsx("span", { children: "Mit drin" }),
            /* @__PURE__ */ a.jsx("ul", { children: W.map((F) => /* @__PURE__ */ a.jsx("li", { children: F }, F)) })
          ] })
        ] });
      }
      case "contact":
        return td(se);
      default:
        return null;
    }
  };
  return /* @__PURE__ */ a.jsxs(
    "div",
    {
      className: `tfdWizard tfdWizard--${r}`,
      style: { "--partner-wizard-primary": n },
      "data-testid": "partner-wizard",
      children: [
        /* @__PURE__ */ a.jsxs("header", { className: "tfdWizard__header", children: [
          /* @__PURE__ */ a.jsx("span", { children: "Händler werden" }),
          /* @__PURE__ */ a.jsx("h2", { children: l.headline }),
          /* @__PURE__ */ a.jsx("p", { children: l.intro })
        ] }),
        o && /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__error", children: o }),
        /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__progress", children: /* @__PURE__ */ a.jsx("div", { style: { width: `${R}%` } }) }),
        /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__layout", children: [
          /* @__PURE__ */ a.jsx("aside", { className: "tfdWizard__steps", "aria-label": "Wizard Schritte", children: K.map((v, x) => {
            const W = [
              x === s ? "active" : "",
              x < s ? "done" : ""
            ].filter(Boolean).join(" ");
            return /* @__PURE__ */ a.jsxs(
              "button",
              {
                type: "button",
                className: W,
                onClick: () => {
                  x <= s && (d(x), _(""));
                },
                "data-testid": `step-${v.key}`,
                children: [
                  /* @__PURE__ */ a.jsx("b", { children: x < s ? /* @__PURE__ */ a.jsx("svg", { viewBox: "0 0 24 24", "aria-hidden": "true", focusable: "false", children: /* @__PURE__ */ a.jsx("path", { d: "M5 12.5l4.2 4.2L19 7" }) }) : x + 1 }),
                  /* @__PURE__ */ a.jsx("span", { children: v.label })
                ]
              },
              v.key
            );
          }) }),
          /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__body", children: [
            /* @__PURE__ */ a.jsx("main", { className: "tfdWizard__mainPane", children: nd() }),
            bc()
          ] })
        ] }),
        /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__actions", children: [
          /* @__PURE__ */ a.jsx(
            "button",
            {
              type: "button",
              onClick: Yc,
              disabled: s === 0,
              "data-testid": "wizard-back-button",
              children: "Zurück"
            }
          ),
          se.type === "contact" ? /* @__PURE__ */ a.jsx(
            "button",
            {
              type: "button",
              onClick: qc,
              disabled: !H || h,
              "data-testid": "wizard-submit-button",
              children: h ? "Senden..." : k.cta
            }
          ) : /* @__PURE__ */ a.jsx(
            "button",
            {
              type: "button",
              onClick: on,
              disabled: !H,
              "data-testid": "wizard-next-button",
              children: "Weiter"
            }
          )
        ] }),
        Jc()
      ]
    }
  );
}
class om extends HTMLElement {
  constructor() {
    super(...arguments);
    ei(this, "root");
    ei(this, "mount");
  }
  connectedCallback() {
    if (this.root)
      return;
    this.mount = document.createElement("div"), this.appendChild(this.mount);
    const n = this.getAttribute("client-id") || "no1", r = this.getAttribute("api-base-url") || "", l = this.getAttribute("primary-color") || "#E31E24", o = (this.getAttribute("theme") || "light") === "dark" ? "dark" : "light";
    this.style.setProperty("--partner-wizard-primary", l), this.root = Bc(this.mount), this.root.render(
      /* @__PURE__ */ a.jsx(
        im,
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
customElements.get("tfd-partner-wizard") || customElements.define("tfd-partner-wizard", om);
export {
  im as PartnerWizard
};
