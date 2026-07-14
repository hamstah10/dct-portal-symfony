var qc = Object.defineProperty;
var bc = (e, t, n) => t in e ? qc(e, t, { enumerable: !0, configurable: !0, writable: !0, value: n }) : e[t] = n;
var Gl = (e, t, n) => bc(e, typeof t != "symbol" ? t + "" : t, n);
var Ts = { exports: {} }, Pl = {}, Ps = { exports: {} }, I = {};
/**
 * @license React
 * react.production.min.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */
var xr = Symbol.for("react.element"), ed = Symbol.for("react.portal"), td = Symbol.for("react.fragment"), nd = Symbol.for("react.strict_mode"), rd = Symbol.for("react.profiler"), ld = Symbol.for("react.provider"), id = Symbol.for("react.context"), od = Symbol.for("react.forward_ref"), ud = Symbol.for("react.suspense"), sd = Symbol.for("react.memo"), ad = Symbol.for("react.lazy"), yu = Symbol.iterator;
function cd(e) {
  return e === null || typeof e != "object" ? null : (e = yu && e[yu] || e["@@iterator"], typeof e == "function" ? e : null);
}
var Ls = { isMounted: function() {
  return !1;
}, enqueueForceUpdate: function() {
}, enqueueReplaceState: function() {
}, enqueueSetState: function() {
} }, Ms = Object.assign, Fs = {};
function Ln(e, t, n) {
  this.props = e, this.context = t, this.refs = Fs, this.updater = n || Ls;
}
Ln.prototype.isReactComponent = {};
Ln.prototype.setState = function(e, t) {
  if (typeof e != "object" && typeof e != "function" && e != null) throw Error("setState(...): takes an object of state variables to update or a function which returns an object of state variables.");
  this.updater.enqueueSetState(this, e, t, "setState");
};
Ln.prototype.forceUpdate = function(e) {
  this.updater.enqueueForceUpdate(this, e, "forceUpdate");
};
function Ws() {
}
Ws.prototype = Ln.prototype;
function go(e, t, n) {
  this.props = e, this.context = t, this.refs = Fs, this.updater = n || Ls;
}
var yo = go.prototype = new Ws();
yo.constructor = go;
Ms(yo, Ln.prototype);
yo.isPureReactComponent = !0;
var wu = Array.isArray, Os = Object.prototype.hasOwnProperty, wo = { current: null }, Rs = { key: !0, ref: !0, __self: !0, __source: !0 };
function As(e, t, n) {
  var r, l = {}, i = null, o = null;
  if (t != null) for (r in t.ref !== void 0 && (o = t.ref), t.key !== void 0 && (i = "" + t.key), t) Os.call(t, r) && !Rs.hasOwnProperty(r) && (l[r] = t[r]);
  var u = arguments.length - 2;
  if (u === 1) l.children = n;
  else if (1 < u) {
    for (var s = Array(u), d = 0; d < u; d++) s[d] = arguments[d + 2];
    l.children = s;
  }
  if (e && e.defaultProps) for (r in u = e.defaultProps, u) l[r] === void 0 && (l[r] = u[r]);
  return { $$typeof: xr, type: e, key: i, ref: o, props: l, _owner: wo.current };
}
function dd(e, t) {
  return { $$typeof: xr, type: e.type, key: t, ref: e.ref, props: e.props, _owner: e._owner };
}
function ko(e) {
  return typeof e == "object" && e !== null && e.$$typeof === xr;
}
function fd(e) {
  var t = { "=": "=0", ":": "=2" };
  return "$" + e.replace(/[=:]/g, function(n) {
    return t[n];
  });
}
var ku = /\/+/g;
function Yl(e, t) {
  return typeof e == "object" && e !== null && e.key != null ? fd("" + e.key) : t.toString(36);
}
function Gr(e, t, n, r, l) {
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
        case xr:
        case ed:
          o = !0;
      }
  }
  if (o) return o = e, l = l(o), e = r === "" ? "." + Yl(o, 0) : r, wu(l) ? (n = "", e != null && (n = e.replace(ku, "$&/") + "/"), Gr(l, t, n, "", function(d) {
    return d;
  })) : l != null && (ko(l) && (l = dd(l, n + (!l.key || o && o.key === l.key ? "" : ("" + l.key).replace(ku, "$&/") + "/") + e)), t.push(l)), 1;
  if (o = 0, r = r === "" ? "." : r + ":", wu(e)) for (var u = 0; u < e.length; u++) {
    i = e[u];
    var s = r + Yl(i, u);
    o += Gr(i, t, n, s, l);
  }
  else if (s = cd(e), typeof s == "function") for (e = s.call(e), u = 0; !(i = e.next()).done; ) i = i.value, s = r + Yl(i, u++), o += Gr(i, t, n, s, l);
  else if (i === "object") throw t = String(e), Error("Objects are not valid as a React child (found: " + (t === "[object Object]" ? "object with keys {" + Object.keys(e).join(", ") + "}" : t) + "). If you meant to render a collection of children, use an array instead.");
  return o;
}
function Tr(e, t, n) {
  if (e == null) return e;
  var r = [], l = 0;
  return Gr(e, r, "", "", function(i) {
    return t.call(n, i, l++);
  }), r;
}
function pd(e) {
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
var _e = { current: null }, Yr = { transition: null }, md = { ReactCurrentDispatcher: _e, ReactCurrentBatchConfig: Yr, ReactCurrentOwner: wo };
function Is() {
  throw Error("act(...) is not supported in production builds of React.");
}
I.Children = { map: Tr, forEach: function(e, t, n) {
  Tr(e, function() {
    t.apply(this, arguments);
  }, n);
}, count: function(e) {
  var t = 0;
  return Tr(e, function() {
    t++;
  }), t;
}, toArray: function(e) {
  return Tr(e, function(t) {
    return t;
  }) || [];
}, only: function(e) {
  if (!ko(e)) throw Error("React.Children.only expected to receive a single React element child.");
  return e;
} };
I.Component = Ln;
I.Fragment = td;
I.Profiler = rd;
I.PureComponent = go;
I.StrictMode = nd;
I.Suspense = ud;
I.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED = md;
I.act = Is;
I.cloneElement = function(e, t, n) {
  if (e == null) throw Error("React.cloneElement(...): The argument must be a React element, but you passed " + e + ".");
  var r = Ms({}, e.props), l = e.key, i = e.ref, o = e._owner;
  if (t != null) {
    if (t.ref !== void 0 && (i = t.ref, o = wo.current), t.key !== void 0 && (l = "" + t.key), e.type && e.type.defaultProps) var u = e.type.defaultProps;
    for (s in t) Os.call(t, s) && !Rs.hasOwnProperty(s) && (r[s] = t[s] === void 0 && u !== void 0 ? u[s] : t[s]);
  }
  var s = arguments.length - 2;
  if (s === 1) r.children = n;
  else if (1 < s) {
    u = Array(s);
    for (var d = 0; d < s; d++) u[d] = arguments[d + 2];
    r.children = u;
  }
  return { $$typeof: xr, type: e.type, key: l, ref: i, props: r, _owner: o };
};
I.createContext = function(e) {
  return e = { $$typeof: id, _currentValue: e, _currentValue2: e, _threadCount: 0, Provider: null, Consumer: null, _defaultValue: null, _globalName: null }, e.Provider = { $$typeof: ld, _context: e }, e.Consumer = e;
};
I.createElement = As;
I.createFactory = function(e) {
  var t = As.bind(null, e);
  return t.type = e, t;
};
I.createRef = function() {
  return { current: null };
};
I.forwardRef = function(e) {
  return { $$typeof: od, render: e };
};
I.isValidElement = ko;
I.lazy = function(e) {
  return { $$typeof: ad, _payload: { _status: -1, _result: e }, _init: pd };
};
I.memo = function(e, t) {
  return { $$typeof: sd, type: e, compare: t === void 0 ? null : t };
};
I.startTransition = function(e) {
  var t = Yr.transition;
  Yr.transition = {};
  try {
    e();
  } finally {
    Yr.transition = t;
  }
};
I.unstable_act = Is;
I.useCallback = function(e, t) {
  return _e.current.useCallback(e, t);
};
I.useContext = function(e) {
  return _e.current.useContext(e);
};
I.useDebugValue = function() {
};
I.useDeferredValue = function(e) {
  return _e.current.useDeferredValue(e);
};
I.useEffect = function(e, t) {
  return _e.current.useEffect(e, t);
};
I.useId = function() {
  return _e.current.useId();
};
I.useImperativeHandle = function(e, t, n) {
  return _e.current.useImperativeHandle(e, t, n);
};
I.useInsertionEffect = function(e, t) {
  return _e.current.useInsertionEffect(e, t);
};
I.useLayoutEffect = function(e, t) {
  return _e.current.useLayoutEffect(e, t);
};
I.useMemo = function(e, t) {
  return _e.current.useMemo(e, t);
};
I.useReducer = function(e, t, n) {
  return _e.current.useReducer(e, t, n);
};
I.useRef = function(e) {
  return _e.current.useRef(e);
};
I.useState = function(e) {
  return _e.current.useState(e);
};
I.useSyncExternalStore = function(e, t, n) {
  return _e.current.useSyncExternalStore(e, t, n);
};
I.useTransition = function() {
  return _e.current.useTransition();
};
I.version = "18.3.1";
Ps.exports = I;
var te = Ps.exports;
/**
 * @license React
 * react-jsx-runtime.production.min.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */
var hd = te, vd = Symbol.for("react.element"), gd = Symbol.for("react.fragment"), yd = Object.prototype.hasOwnProperty, wd = hd.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED.ReactCurrentOwner, kd = { key: !0, ref: !0, __self: !0, __source: !0 };
function Ds(e, t, n) {
  var r, l = {}, i = null, o = null;
  n !== void 0 && (i = "" + n), t.key !== void 0 && (i = "" + t.key), t.ref !== void 0 && (o = t.ref);
  for (r in t) yd.call(t, r) && !kd.hasOwnProperty(r) && (l[r] = t[r]);
  if (e && e.defaultProps) for (r in t = e.defaultProps, t) l[r] === void 0 && (l[r] = t[r]);
  return { $$typeof: vd, type: e, key: i, ref: o, props: l, _owner: wd.current };
}
Pl.Fragment = gd;
Pl.jsx = Ds;
Pl.jsxs = Ds;
Ts.exports = Pl;
var a = Ts.exports, Us = { exports: {} }, Re = {}, $s = { exports: {} }, Vs = {};
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
  function t(N, W) {
    var O = N.length;
    N.push(W);
    e: for (; 0 < O; ) {
      var X = O - 1 >>> 1, le = N[X];
      if (0 < l(le, W)) N[X] = W, N[O] = le, O = X;
      else break e;
    }
  }
  function n(N) {
    return N.length === 0 ? null : N[0];
  }
  function r(N) {
    if (N.length === 0) return null;
    var W = N[0], O = N.pop();
    if (O !== W) {
      N[0] = O;
      e: for (var X = 0, le = N.length, Ut = le >>> 1; X < Ut; ) {
        var it = 2 * (X + 1) - 1, Rn = N[it], ot = it + 1, nn = N[ot];
        if (0 > l(Rn, O)) ot < le && 0 > l(nn, Rn) ? (N[X] = nn, N[ot] = O, X = ot) : (N[X] = Rn, N[it] = O, X = it);
        else if (ot < le && 0 > l(nn, O)) N[X] = nn, N[ot] = O, X = ot;
        else break e;
      }
    }
    return W;
  }
  function l(N, W) {
    var O = N.sortIndex - W.sortIndex;
    return O !== 0 ? O : N.id - W.id;
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
  var s = [], d = [], m = 1, v = null, h = 3, _ = !1, x = !1, k = !1, K = typeof setTimeout == "function" ? setTimeout : null, f = typeof clearTimeout == "function" ? clearTimeout : null, c = typeof setImmediate < "u" ? setImmediate : null;
  typeof navigator < "u" && navigator.scheduling !== void 0 && navigator.scheduling.isInputPending !== void 0 && navigator.scheduling.isInputPending.bind(navigator.scheduling);
  function p(N) {
    for (var W = n(d); W !== null; ) {
      if (W.callback === null) r(d);
      else if (W.startTime <= N) r(d), W.sortIndex = W.expirationTime, t(s, W);
      else break;
      W = n(d);
    }
  }
  function y(N) {
    if (k = !1, p(N), !x) if (n(s) !== null) x = !0, ae(E);
    else {
      var W = n(d);
      W !== null && b(y, W.startTime - N);
    }
  }
  function E(N, W) {
    x = !1, k && (k = !1, f(P), P = -1), _ = !0;
    var O = h;
    try {
      for (p(W), v = n(s); v !== null && (!(v.expirationTime > W) || N && !se()); ) {
        var X = v.callback;
        if (typeof X == "function") {
          v.callback = null, h = v.priorityLevel;
          var le = X(v.expirationTime <= W);
          W = e.unstable_now(), typeof le == "function" ? v.callback = le : v === n(s) && r(s), p(W);
        } else r(s);
        v = n(s);
      }
      if (v !== null) var Ut = !0;
      else {
        var it = n(d);
        it !== null && b(y, it.startTime - W), Ut = !1;
      }
      return Ut;
    } finally {
      v = null, h = O, _ = !1;
    }
  }
  var z = !1, T = null, P = -1, V = 5, R = -1;
  function se() {
    return !(e.unstable_now() - R < V);
  }
  function It() {
    if (T !== null) {
      var N = e.unstable_now();
      R = N;
      var W = !0;
      try {
        W = T(!0, N);
      } finally {
        W ? Dt() : (z = !1, T = null);
      }
    } else z = !1;
  }
  var Dt;
  if (typeof c == "function") Dt = function() {
    c(It);
  };
  else if (typeof MessageChannel < "u") {
    var Wn = new MessageChannel(), On = Wn.port2;
    Wn.port1.onmessage = It, Dt = function() {
      On.postMessage(null);
    };
  } else Dt = function() {
    K(It, 0);
  };
  function ae(N) {
    T = N, z || (z = !0, Dt());
  }
  function b(N, W) {
    P = K(function() {
      N(e.unstable_now());
    }, W);
  }
  e.unstable_IdlePriority = 5, e.unstable_ImmediatePriority = 1, e.unstable_LowPriority = 4, e.unstable_NormalPriority = 3, e.unstable_Profiling = null, e.unstable_UserBlockingPriority = 2, e.unstable_cancelCallback = function(N) {
    N.callback = null;
  }, e.unstable_continueExecution = function() {
    x || _ || (x = !0, ae(E));
  }, e.unstable_forceFrameRate = function(N) {
    0 > N || 125 < N ? console.error("forceFrameRate takes a positive int between 0 and 125, forcing frame rates higher than 125 fps is not supported") : V = 0 < N ? Math.floor(1e3 / N) : 5;
  }, e.unstable_getCurrentPriorityLevel = function() {
    return h;
  }, e.unstable_getFirstCallbackNode = function() {
    return n(s);
  }, e.unstable_next = function(N) {
    switch (h) {
      case 1:
      case 2:
      case 3:
        var W = 3;
        break;
      default:
        W = h;
    }
    var O = h;
    h = W;
    try {
      return N();
    } finally {
      h = O;
    }
  }, e.unstable_pauseExecution = function() {
  }, e.unstable_requestPaint = function() {
  }, e.unstable_runWithPriority = function(N, W) {
    switch (N) {
      case 1:
      case 2:
      case 3:
      case 4:
      case 5:
        break;
      default:
        N = 3;
    }
    var O = h;
    h = N;
    try {
      return W();
    } finally {
      h = O;
    }
  }, e.unstable_scheduleCallback = function(N, W, O) {
    var X = e.unstable_now();
    switch (typeof O == "object" && O !== null ? (O = O.delay, O = typeof O == "number" && 0 < O ? X + O : X) : O = X, N) {
      case 1:
        var le = -1;
        break;
      case 2:
        le = 250;
        break;
      case 5:
        le = 1073741823;
        break;
      case 4:
        le = 1e4;
        break;
      default:
        le = 5e3;
    }
    return le = O + le, N = { id: m++, callback: W, priorityLevel: N, startTime: O, expirationTime: le, sortIndex: -1 }, O > X ? (N.sortIndex = O, t(d, N), n(s) === null && N === n(d) && (k ? (f(P), P = -1) : k = !0, b(y, O - X))) : (N.sortIndex = le, t(s, N), x || _ || (x = !0, ae(E))), N;
  }, e.unstable_shouldYield = se, e.unstable_wrapCallback = function(N) {
    var W = h;
    return function() {
      var O = h;
      h = W;
      try {
        return N.apply(this, arguments);
      } finally {
        h = O;
      }
    };
  };
})(Vs);
$s.exports = Vs;
var Sd = $s.exports;
/**
 * @license React
 * react-dom.production.min.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */
var _d = te, Oe = Sd;
function w(e) {
  for (var t = "https://reactjs.org/docs/error-decoder.html?invariant=" + e, n = 1; n < arguments.length; n++) t += "&args[]=" + encodeURIComponent(arguments[n]);
  return "Minified React error #" + e + "; visit " + t + " for the full message or use the non-minified dev environment for full errors and additional helpful warnings.";
}
var Bs = /* @__PURE__ */ new Set(), or = {};
function en(e, t) {
  zn(e, t), zn(e + "Capture", t);
}
function zn(e, t) {
  for (or[e] = t, e = 0; e < t.length; e++) Bs.add(t[e]);
}
var ft = !(typeof window > "u" || typeof window.document > "u" || typeof window.document.createElement > "u"), xi = Object.prototype.hasOwnProperty, xd = /^[:A-Z_a-z\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u02FF\u0370-\u037D\u037F-\u1FFF\u200C-\u200D\u2070-\u218F\u2C00-\u2FEF\u3001-\uD7FF\uF900-\uFDCF\uFDF0-\uFFFD][:A-Z_a-z\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u02FF\u0370-\u037D\u037F-\u1FFF\u200C-\u200D\u2070-\u218F\u2C00-\u2FEF\u3001-\uD7FF\uF900-\uFDCF\uFDF0-\uFFFD\-.0-9\u00B7\u0300-\u036F\u203F-\u2040]*$/, Su = {}, _u = {};
function zd(e) {
  return xi.call(_u, e) ? !0 : xi.call(Su, e) ? !1 : xd.test(e) ? _u[e] = !0 : (Su[e] = !0, !1);
}
function Ed(e, t, n, r) {
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
function Nd(e, t, n, r) {
  if (t === null || typeof t > "u" || Ed(e, t, n, r)) return !0;
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
function xe(e, t, n, r, l, i, o) {
  this.acceptsBooleans = t === 2 || t === 3 || t === 4, this.attributeName = r, this.attributeNamespace = l, this.mustUseProperty = n, this.propertyName = e, this.type = t, this.sanitizeURL = i, this.removeEmptyString = o;
}
var he = {};
"children dangerouslySetInnerHTML defaultValue defaultChecked innerHTML suppressContentEditableWarning suppressHydrationWarning style".split(" ").forEach(function(e) {
  he[e] = new xe(e, 0, !1, e, null, !1, !1);
});
[["acceptCharset", "accept-charset"], ["className", "class"], ["htmlFor", "for"], ["httpEquiv", "http-equiv"]].forEach(function(e) {
  var t = e[0];
  he[t] = new xe(t, 1, !1, e[1], null, !1, !1);
});
["contentEditable", "draggable", "spellCheck", "value"].forEach(function(e) {
  he[e] = new xe(e, 2, !1, e.toLowerCase(), null, !1, !1);
});
["autoReverse", "externalResourcesRequired", "focusable", "preserveAlpha"].forEach(function(e) {
  he[e] = new xe(e, 2, !1, e, null, !1, !1);
});
"allowFullScreen async autoFocus autoPlay controls default defer disabled disablePictureInPicture disableRemotePlayback formNoValidate hidden loop noModule noValidate open playsInline readOnly required reversed scoped seamless itemScope".split(" ").forEach(function(e) {
  he[e] = new xe(e, 3, !1, e.toLowerCase(), null, !1, !1);
});
["checked", "multiple", "muted", "selected"].forEach(function(e) {
  he[e] = new xe(e, 3, !0, e, null, !1, !1);
});
["capture", "download"].forEach(function(e) {
  he[e] = new xe(e, 4, !1, e, null, !1, !1);
});
["cols", "rows", "size", "span"].forEach(function(e) {
  he[e] = new xe(e, 6, !1, e, null, !1, !1);
});
["rowSpan", "start"].forEach(function(e) {
  he[e] = new xe(e, 5, !1, e.toLowerCase(), null, !1, !1);
});
var So = /[\-:]([a-z])/g;
function _o(e) {
  return e[1].toUpperCase();
}
"accent-height alignment-baseline arabic-form baseline-shift cap-height clip-path clip-rule color-interpolation color-interpolation-filters color-profile color-rendering dominant-baseline enable-background fill-opacity fill-rule flood-color flood-opacity font-family font-size font-size-adjust font-stretch font-style font-variant font-weight glyph-name glyph-orientation-horizontal glyph-orientation-vertical horiz-adv-x horiz-origin-x image-rendering letter-spacing lighting-color marker-end marker-mid marker-start overline-position overline-thickness paint-order panose-1 pointer-events rendering-intent shape-rendering stop-color stop-opacity strikethrough-position strikethrough-thickness stroke-dasharray stroke-dashoffset stroke-linecap stroke-linejoin stroke-miterlimit stroke-opacity stroke-width text-anchor text-decoration text-rendering underline-position underline-thickness unicode-bidi unicode-range units-per-em v-alphabetic v-hanging v-ideographic v-mathematical vector-effect vert-adv-y vert-origin-x vert-origin-y word-spacing writing-mode xmlns:xlink x-height".split(" ").forEach(function(e) {
  var t = e.replace(
    So,
    _o
  );
  he[t] = new xe(t, 1, !1, e, null, !1, !1);
});
"xlink:actuate xlink:arcrole xlink:role xlink:show xlink:title xlink:type".split(" ").forEach(function(e) {
  var t = e.replace(So, _o);
  he[t] = new xe(t, 1, !1, e, "http://www.w3.org/1999/xlink", !1, !1);
});
["xml:base", "xml:lang", "xml:space"].forEach(function(e) {
  var t = e.replace(So, _o);
  he[t] = new xe(t, 1, !1, e, "http://www.w3.org/XML/1998/namespace", !1, !1);
});
["tabIndex", "crossOrigin"].forEach(function(e) {
  he[e] = new xe(e, 1, !1, e.toLowerCase(), null, !1, !1);
});
he.xlinkHref = new xe("xlinkHref", 1, !1, "xlink:href", "http://www.w3.org/1999/xlink", !0, !1);
["src", "href", "action", "formAction"].forEach(function(e) {
  he[e] = new xe(e, 1, !1, e.toLowerCase(), null, !0, !0);
});
function xo(e, t, n, r) {
  var l = he.hasOwnProperty(t) ? he[t] : null;
  (l !== null ? l.type !== 0 : r || !(2 < t.length) || t[0] !== "o" && t[0] !== "O" || t[1] !== "n" && t[1] !== "N") && (Nd(t, n, l, r) && (n = null), r || l === null ? zd(t) && (n === null ? e.removeAttribute(t) : e.setAttribute(t, "" + n)) : l.mustUseProperty ? e[l.propertyName] = n === null ? l.type === 3 ? !1 : "" : n : (t = l.attributeName, r = l.attributeNamespace, n === null ? e.removeAttribute(t) : (l = l.type, n = l === 3 || l === 4 && n === !0 ? "" : "" + n, r ? e.setAttributeNS(r, t, n) : e.setAttribute(t, n))));
}
var vt = _d.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED, Pr = Symbol.for("react.element"), on = Symbol.for("react.portal"), un = Symbol.for("react.fragment"), zo = Symbol.for("react.strict_mode"), zi = Symbol.for("react.profiler"), Ks = Symbol.for("react.provider"), Hs = Symbol.for("react.context"), Eo = Symbol.for("react.forward_ref"), Ei = Symbol.for("react.suspense"), Ni = Symbol.for("react.suspense_list"), No = Symbol.for("react.memo"), wt = Symbol.for("react.lazy"), Qs = Symbol.for("react.offscreen"), xu = Symbol.iterator;
function An(e) {
  return e === null || typeof e != "object" ? null : (e = xu && e[xu] || e["@@iterator"], typeof e == "function" ? e : null);
}
var q = Object.assign, Xl;
function Qn(e) {
  if (Xl === void 0) try {
    throw Error();
  } catch (n) {
    var t = n.stack.trim().match(/\n( *(at )?)/);
    Xl = t && t[1] || "";
  }
  return `
` + Xl + e;
}
var Zl = !1;
function Jl(e, t) {
  if (!e || Zl) return "";
  Zl = !0;
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
    Zl = !1, Error.prepareStackTrace = n;
  }
  return (e = e ? e.displayName || e.name : "") ? Qn(e) : "";
}
function Cd(e) {
  switch (e.tag) {
    case 5:
      return Qn(e.type);
    case 16:
      return Qn("Lazy");
    case 13:
      return Qn("Suspense");
    case 19:
      return Qn("SuspenseList");
    case 0:
    case 2:
    case 15:
      return e = Jl(e.type, !1), e;
    case 11:
      return e = Jl(e.type.render, !1), e;
    case 1:
      return e = Jl(e.type, !0), e;
    default:
      return "";
  }
}
function Ci(e) {
  if (e == null) return null;
  if (typeof e == "function") return e.displayName || e.name || null;
  if (typeof e == "string") return e;
  switch (e) {
    case un:
      return "Fragment";
    case on:
      return "Portal";
    case zi:
      return "Profiler";
    case zo:
      return "StrictMode";
    case Ei:
      return "Suspense";
    case Ni:
      return "SuspenseList";
  }
  if (typeof e == "object") switch (e.$$typeof) {
    case Hs:
      return (e.displayName || "Context") + ".Consumer";
    case Ks:
      return (e._context.displayName || "Context") + ".Provider";
    case Eo:
      var t = e.render;
      return e = e.displayName, e || (e = t.displayName || t.name || "", e = e !== "" ? "ForwardRef(" + e + ")" : "ForwardRef"), e;
    case No:
      return t = e.displayName || null, t !== null ? t : Ci(e.type) || "Memo";
    case wt:
      t = e._payload, e = e._init;
      try {
        return Ci(e(t));
      } catch {
      }
  }
  return null;
}
function jd(e) {
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
      return Ci(t);
    case 8:
      return t === zo ? "StrictMode" : "Mode";
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
function Gs(e) {
  var t = e.type;
  return (e = e.nodeName) && e.toLowerCase() === "input" && (t === "checkbox" || t === "radio");
}
function Td(e) {
  var t = Gs(e) ? "checked" : "value", n = Object.getOwnPropertyDescriptor(e.constructor.prototype, t), r = "" + e[t];
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
function Lr(e) {
  e._valueTracker || (e._valueTracker = Td(e));
}
function Ys(e) {
  if (!e) return !1;
  var t = e._valueTracker;
  if (!t) return !0;
  var n = t.getValue(), r = "";
  return e && (r = Gs(e) ? e.checked ? "true" : "false" : e.value), e = r, e !== n ? (t.setValue(e), !0) : !1;
}
function ol(e) {
  if (e = e || (typeof document < "u" ? document : void 0), typeof e > "u") return null;
  try {
    return e.activeElement || e.body;
  } catch {
    return e.body;
  }
}
function ji(e, t) {
  var n = t.checked;
  return q({}, t, { defaultChecked: void 0, defaultValue: void 0, value: void 0, checked: n ?? e._wrapperState.initialChecked });
}
function zu(e, t) {
  var n = t.defaultValue == null ? "" : t.defaultValue, r = t.checked != null ? t.checked : t.defaultChecked;
  n = Ft(t.value != null ? t.value : n), e._wrapperState = { initialChecked: r, initialValue: n, controlled: t.type === "checkbox" || t.type === "radio" ? t.checked != null : t.value != null };
}
function Xs(e, t) {
  t = t.checked, t != null && xo(e, "checked", t, !1);
}
function Ti(e, t) {
  Xs(e, t);
  var n = Ft(t.value), r = t.type;
  if (n != null) r === "number" ? (n === 0 && e.value === "" || e.value != n) && (e.value = "" + n) : e.value !== "" + n && (e.value = "" + n);
  else if (r === "submit" || r === "reset") {
    e.removeAttribute("value");
    return;
  }
  t.hasOwnProperty("value") ? Pi(e, t.type, n) : t.hasOwnProperty("defaultValue") && Pi(e, t.type, Ft(t.defaultValue)), t.checked == null && t.defaultChecked != null && (e.defaultChecked = !!t.defaultChecked);
}
function Eu(e, t, n) {
  if (t.hasOwnProperty("value") || t.hasOwnProperty("defaultValue")) {
    var r = t.type;
    if (!(r !== "submit" && r !== "reset" || t.value !== void 0 && t.value !== null)) return;
    t = "" + e._wrapperState.initialValue, n || t === e.value || (e.value = t), e.defaultValue = t;
  }
  n = e.name, n !== "" && (e.name = ""), e.defaultChecked = !!e._wrapperState.initialChecked, n !== "" && (e.name = n);
}
function Pi(e, t, n) {
  (t !== "number" || ol(e.ownerDocument) !== e) && (n == null ? e.defaultValue = "" + e._wrapperState.initialValue : e.defaultValue !== "" + n && (e.defaultValue = "" + n));
}
var Gn = Array.isArray;
function yn(e, t, n, r) {
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
function Li(e, t) {
  if (t.dangerouslySetInnerHTML != null) throw Error(w(91));
  return q({}, t, { value: void 0, defaultValue: void 0, children: "" + e._wrapperState.initialValue });
}
function Nu(e, t) {
  var n = t.value;
  if (n == null) {
    if (n = t.children, t = t.defaultValue, n != null) {
      if (t != null) throw Error(w(92));
      if (Gn(n)) {
        if (1 < n.length) throw Error(w(93));
        n = n[0];
      }
      t = n;
    }
    t == null && (t = ""), n = t;
  }
  e._wrapperState = { initialValue: Ft(n) };
}
function Zs(e, t) {
  var n = Ft(t.value), r = Ft(t.defaultValue);
  n != null && (n = "" + n, n !== e.value && (e.value = n), t.defaultValue == null && e.defaultValue !== n && (e.defaultValue = n)), r != null && (e.defaultValue = "" + r);
}
function Cu(e) {
  var t = e.textContent;
  t === e._wrapperState.initialValue && t !== "" && t !== null && (e.value = t);
}
function Js(e) {
  switch (e) {
    case "svg":
      return "http://www.w3.org/2000/svg";
    case "math":
      return "http://www.w3.org/1998/Math/MathML";
    default:
      return "http://www.w3.org/1999/xhtml";
  }
}
function Mi(e, t) {
  return e == null || e === "http://www.w3.org/1999/xhtml" ? Js(t) : e === "http://www.w3.org/2000/svg" && t === "foreignObject" ? "http://www.w3.org/1999/xhtml" : e;
}
var Mr, qs = function(e) {
  return typeof MSApp < "u" && MSApp.execUnsafeLocalFunction ? function(t, n, r, l) {
    MSApp.execUnsafeLocalFunction(function() {
      return e(t, n, r, l);
    });
  } : e;
}(function(e, t) {
  if (e.namespaceURI !== "http://www.w3.org/2000/svg" || "innerHTML" in e) e.innerHTML = t;
  else {
    for (Mr = Mr || document.createElement("div"), Mr.innerHTML = "<svg>" + t.valueOf().toString() + "</svg>", t = Mr.firstChild; e.firstChild; ) e.removeChild(e.firstChild);
    for (; t.firstChild; ) e.appendChild(t.firstChild);
  }
});
function ur(e, t) {
  if (t) {
    var n = e.firstChild;
    if (n && n === e.lastChild && n.nodeType === 3) {
      n.nodeValue = t;
      return;
    }
  }
  e.textContent = t;
}
var Jn = {
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
}, Pd = ["Webkit", "ms", "Moz", "O"];
Object.keys(Jn).forEach(function(e) {
  Pd.forEach(function(t) {
    t = t + e.charAt(0).toUpperCase() + e.substring(1), Jn[t] = Jn[e];
  });
});
function bs(e, t, n) {
  return t == null || typeof t == "boolean" || t === "" ? "" : n || typeof t != "number" || t === 0 || Jn.hasOwnProperty(e) && Jn[e] ? ("" + t).trim() : t + "px";
}
function ea(e, t) {
  e = e.style;
  for (var n in t) if (t.hasOwnProperty(n)) {
    var r = n.indexOf("--") === 0, l = bs(n, t[n], r);
    n === "float" && (n = "cssFloat"), r ? e.setProperty(n, l) : e[n] = l;
  }
}
var Ld = q({ menuitem: !0 }, { area: !0, base: !0, br: !0, col: !0, embed: !0, hr: !0, img: !0, input: !0, keygen: !0, link: !0, meta: !0, param: !0, source: !0, track: !0, wbr: !0 });
function Fi(e, t) {
  if (t) {
    if (Ld[e] && (t.children != null || t.dangerouslySetInnerHTML != null)) throw Error(w(137, e));
    if (t.dangerouslySetInnerHTML != null) {
      if (t.children != null) throw Error(w(60));
      if (typeof t.dangerouslySetInnerHTML != "object" || !("__html" in t.dangerouslySetInnerHTML)) throw Error(w(61));
    }
    if (t.style != null && typeof t.style != "object") throw Error(w(62));
  }
}
function Wi(e, t) {
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
var Oi = null;
function Co(e) {
  return e = e.target || e.srcElement || window, e.correspondingUseElement && (e = e.correspondingUseElement), e.nodeType === 3 ? e.parentNode : e;
}
var Ri = null, wn = null, kn = null;
function ju(e) {
  if (e = Nr(e)) {
    if (typeof Ri != "function") throw Error(w(280));
    var t = e.stateNode;
    t && (t = Ol(t), Ri(e.stateNode, e.type, t));
  }
}
function ta(e) {
  wn ? kn ? kn.push(e) : kn = [e] : wn = e;
}
function na() {
  if (wn) {
    var e = wn, t = kn;
    if (kn = wn = null, ju(e), t) for (e = 0; e < t.length; e++) ju(t[e]);
  }
}
function ra(e, t) {
  return e(t);
}
function la() {
}
var ql = !1;
function ia(e, t, n) {
  if (ql) return e(t, n);
  ql = !0;
  try {
    return ra(e, t, n);
  } finally {
    ql = !1, (wn !== null || kn !== null) && (la(), na());
  }
}
function sr(e, t) {
  var n = e.stateNode;
  if (n === null) return null;
  var r = Ol(n);
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
var Ai = !1;
if (ft) try {
  var In = {};
  Object.defineProperty(In, "passive", { get: function() {
    Ai = !0;
  } }), window.addEventListener("test", In, In), window.removeEventListener("test", In, In);
} catch {
  Ai = !1;
}
function Md(e, t, n, r, l, i, o, u, s) {
  var d = Array.prototype.slice.call(arguments, 3);
  try {
    t.apply(n, d);
  } catch (m) {
    this.onError(m);
  }
}
var qn = !1, ul = null, sl = !1, Ii = null, Fd = { onError: function(e) {
  qn = !0, ul = e;
} };
function Wd(e, t, n, r, l, i, o, u, s) {
  qn = !1, ul = null, Md.apply(Fd, arguments);
}
function Od(e, t, n, r, l, i, o, u, s) {
  if (Wd.apply(this, arguments), qn) {
    if (qn) {
      var d = ul;
      qn = !1, ul = null;
    } else throw Error(w(198));
    sl || (sl = !0, Ii = d);
  }
}
function tn(e) {
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
function oa(e) {
  if (e.tag === 13) {
    var t = e.memoizedState;
    if (t === null && (e = e.alternate, e !== null && (t = e.memoizedState)), t !== null) return t.dehydrated;
  }
  return null;
}
function Tu(e) {
  if (tn(e) !== e) throw Error(w(188));
}
function Rd(e) {
  var t = e.alternate;
  if (!t) {
    if (t = tn(e), t === null) throw Error(w(188));
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
        if (i === n) return Tu(l), e;
        if (i === r) return Tu(l), t;
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
function ua(e) {
  return e = Rd(e), e !== null ? sa(e) : null;
}
function sa(e) {
  if (e.tag === 5 || e.tag === 6) return e;
  for (e = e.child; e !== null; ) {
    var t = sa(e);
    if (t !== null) return t;
    e = e.sibling;
  }
  return null;
}
var aa = Oe.unstable_scheduleCallback, Pu = Oe.unstable_cancelCallback, Ad = Oe.unstable_shouldYield, Id = Oe.unstable_requestPaint, re = Oe.unstable_now, Dd = Oe.unstable_getCurrentPriorityLevel, jo = Oe.unstable_ImmediatePriority, ca = Oe.unstable_UserBlockingPriority, al = Oe.unstable_NormalPriority, Ud = Oe.unstable_LowPriority, da = Oe.unstable_IdlePriority, Ll = null, nt = null;
function $d(e) {
  if (nt && typeof nt.onCommitFiberRoot == "function") try {
    nt.onCommitFiberRoot(Ll, e, void 0, (e.current.flags & 128) === 128);
  } catch {
  }
}
var Ze = Math.clz32 ? Math.clz32 : Kd, Vd = Math.log, Bd = Math.LN2;
function Kd(e) {
  return e >>>= 0, e === 0 ? 32 : 31 - (Vd(e) / Bd | 0) | 0;
}
var Fr = 64, Wr = 4194304;
function Yn(e) {
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
function cl(e, t) {
  var n = e.pendingLanes;
  if (n === 0) return 0;
  var r = 0, l = e.suspendedLanes, i = e.pingedLanes, o = n & 268435455;
  if (o !== 0) {
    var u = o & ~l;
    u !== 0 ? r = Yn(u) : (i &= o, i !== 0 && (r = Yn(i)));
  } else o = n & ~l, o !== 0 ? r = Yn(o) : i !== 0 && (r = Yn(i));
  if (r === 0) return 0;
  if (t !== 0 && t !== r && !(t & l) && (l = r & -r, i = t & -t, l >= i || l === 16 && (i & 4194240) !== 0)) return t;
  if (r & 4 && (r |= n & 16), t = e.entangledLanes, t !== 0) for (e = e.entanglements, t &= r; 0 < t; ) n = 31 - Ze(t), l = 1 << n, r |= e[n], t &= ~l;
  return r;
}
function Hd(e, t) {
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
function Qd(e, t) {
  for (var n = e.suspendedLanes, r = e.pingedLanes, l = e.expirationTimes, i = e.pendingLanes; 0 < i; ) {
    var o = 31 - Ze(i), u = 1 << o, s = l[o];
    s === -1 ? (!(u & n) || u & r) && (l[o] = Hd(u, t)) : s <= t && (e.expiredLanes |= u), i &= ~u;
  }
}
function Di(e) {
  return e = e.pendingLanes & -1073741825, e !== 0 ? e : e & 1073741824 ? 1073741824 : 0;
}
function fa() {
  var e = Fr;
  return Fr <<= 1, !(Fr & 4194240) && (Fr = 64), e;
}
function bl(e) {
  for (var t = [], n = 0; 31 > n; n++) t.push(e);
  return t;
}
function zr(e, t, n) {
  e.pendingLanes |= t, t !== 536870912 && (e.suspendedLanes = 0, e.pingedLanes = 0), e = e.eventTimes, t = 31 - Ze(t), e[t] = n;
}
function Gd(e, t) {
  var n = e.pendingLanes & ~t;
  e.pendingLanes = t, e.suspendedLanes = 0, e.pingedLanes = 0, e.expiredLanes &= t, e.mutableReadLanes &= t, e.entangledLanes &= t, t = e.entanglements;
  var r = e.eventTimes;
  for (e = e.expirationTimes; 0 < n; ) {
    var l = 31 - Ze(n), i = 1 << l;
    t[l] = 0, r[l] = -1, e[l] = -1, n &= ~i;
  }
}
function To(e, t) {
  var n = e.entangledLanes |= t;
  for (e = e.entanglements; n; ) {
    var r = 31 - Ze(n), l = 1 << r;
    l & t | e[r] & t && (e[r] |= t), n &= ~l;
  }
}
var $ = 0;
function pa(e) {
  return e &= -e, 1 < e ? 4 < e ? e & 268435455 ? 16 : 536870912 : 4 : 1;
}
var ma, Po, ha, va, ga, Ui = !1, Or = [], Et = null, Nt = null, Ct = null, ar = /* @__PURE__ */ new Map(), cr = /* @__PURE__ */ new Map(), St = [], Yd = "mousedown mouseup touchcancel touchend touchstart auxclick dblclick pointercancel pointerdown pointerup dragend dragstart drop compositionend compositionstart keydown keypress keyup input textInput copy cut paste click change contextmenu reset submit".split(" ");
function Lu(e, t) {
  switch (e) {
    case "focusin":
    case "focusout":
      Et = null;
      break;
    case "dragenter":
    case "dragleave":
      Nt = null;
      break;
    case "mouseover":
    case "mouseout":
      Ct = null;
      break;
    case "pointerover":
    case "pointerout":
      ar.delete(t.pointerId);
      break;
    case "gotpointercapture":
    case "lostpointercapture":
      cr.delete(t.pointerId);
  }
}
function Dn(e, t, n, r, l, i) {
  return e === null || e.nativeEvent !== i ? (e = { blockedOn: t, domEventName: n, eventSystemFlags: r, nativeEvent: i, targetContainers: [l] }, t !== null && (t = Nr(t), t !== null && Po(t)), e) : (e.eventSystemFlags |= r, t = e.targetContainers, l !== null && t.indexOf(l) === -1 && t.push(l), e);
}
function Xd(e, t, n, r, l) {
  switch (t) {
    case "focusin":
      return Et = Dn(Et, e, t, n, r, l), !0;
    case "dragenter":
      return Nt = Dn(Nt, e, t, n, r, l), !0;
    case "mouseover":
      return Ct = Dn(Ct, e, t, n, r, l), !0;
    case "pointerover":
      var i = l.pointerId;
      return ar.set(i, Dn(ar.get(i) || null, e, t, n, r, l)), !0;
    case "gotpointercapture":
      return i = l.pointerId, cr.set(i, Dn(cr.get(i) || null, e, t, n, r, l)), !0;
  }
  return !1;
}
function ya(e) {
  var t = Kt(e.target);
  if (t !== null) {
    var n = tn(t);
    if (n !== null) {
      if (t = n.tag, t === 13) {
        if (t = oa(n), t !== null) {
          e.blockedOn = t, ga(e.priority, function() {
            ha(n);
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
function Xr(e) {
  if (e.blockedOn !== null) return !1;
  for (var t = e.targetContainers; 0 < t.length; ) {
    var n = $i(e.domEventName, e.eventSystemFlags, t[0], e.nativeEvent);
    if (n === null) {
      n = e.nativeEvent;
      var r = new n.constructor(n.type, n);
      Oi = r, n.target.dispatchEvent(r), Oi = null;
    } else return t = Nr(n), t !== null && Po(t), e.blockedOn = n, !1;
    t.shift();
  }
  return !0;
}
function Mu(e, t, n) {
  Xr(e) && n.delete(t);
}
function Zd() {
  Ui = !1, Et !== null && Xr(Et) && (Et = null), Nt !== null && Xr(Nt) && (Nt = null), Ct !== null && Xr(Ct) && (Ct = null), ar.forEach(Mu), cr.forEach(Mu);
}
function Un(e, t) {
  e.blockedOn === t && (e.blockedOn = null, Ui || (Ui = !0, Oe.unstable_scheduleCallback(Oe.unstable_NormalPriority, Zd)));
}
function dr(e) {
  function t(l) {
    return Un(l, e);
  }
  if (0 < Or.length) {
    Un(Or[0], e);
    for (var n = 1; n < Or.length; n++) {
      var r = Or[n];
      r.blockedOn === e && (r.blockedOn = null);
    }
  }
  for (Et !== null && Un(Et, e), Nt !== null && Un(Nt, e), Ct !== null && Un(Ct, e), ar.forEach(t), cr.forEach(t), n = 0; n < St.length; n++) r = St[n], r.blockedOn === e && (r.blockedOn = null);
  for (; 0 < St.length && (n = St[0], n.blockedOn === null); ) ya(n), n.blockedOn === null && St.shift();
}
var Sn = vt.ReactCurrentBatchConfig, dl = !0;
function Jd(e, t, n, r) {
  var l = $, i = Sn.transition;
  Sn.transition = null;
  try {
    $ = 1, Lo(e, t, n, r);
  } finally {
    $ = l, Sn.transition = i;
  }
}
function qd(e, t, n, r) {
  var l = $, i = Sn.transition;
  Sn.transition = null;
  try {
    $ = 4, Lo(e, t, n, r);
  } finally {
    $ = l, Sn.transition = i;
  }
}
function Lo(e, t, n, r) {
  if (dl) {
    var l = $i(e, t, n, r);
    if (l === null) ai(e, t, r, fl, n), Lu(e, r);
    else if (Xd(l, e, t, n, r)) r.stopPropagation();
    else if (Lu(e, r), t & 4 && -1 < Yd.indexOf(e)) {
      for (; l !== null; ) {
        var i = Nr(l);
        if (i !== null && ma(i), i = $i(e, t, n, r), i === null && ai(e, t, r, fl, n), i === l) break;
        l = i;
      }
      l !== null && r.stopPropagation();
    } else ai(e, t, r, null, n);
  }
}
var fl = null;
function $i(e, t, n, r) {
  if (fl = null, e = Co(r), e = Kt(e), e !== null) if (t = tn(e), t === null) e = null;
  else if (n = t.tag, n === 13) {
    if (e = oa(t), e !== null) return e;
    e = null;
  } else if (n === 3) {
    if (t.stateNode.current.memoizedState.isDehydrated) return t.tag === 3 ? t.stateNode.containerInfo : null;
    e = null;
  } else t !== e && (e = null);
  return fl = e, null;
}
function wa(e) {
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
      switch (Dd()) {
        case jo:
          return 1;
        case ca:
          return 4;
        case al:
        case Ud:
          return 16;
        case da:
          return 536870912;
        default:
          return 16;
      }
    default:
      return 16;
  }
}
var xt = null, Mo = null, Zr = null;
function ka() {
  if (Zr) return Zr;
  var e, t = Mo, n = t.length, r, l = "value" in xt ? xt.value : xt.textContent, i = l.length;
  for (e = 0; e < n && t[e] === l[e]; e++) ;
  var o = n - e;
  for (r = 1; r <= o && t[n - r] === l[i - r]; r++) ;
  return Zr = l.slice(e, 1 < r ? 1 - r : void 0);
}
function Jr(e) {
  var t = e.keyCode;
  return "charCode" in e ? (e = e.charCode, e === 0 && t === 13 && (e = 13)) : e = t, e === 10 && (e = 13), 32 <= e || e === 13 ? e : 0;
}
function Rr() {
  return !0;
}
function Fu() {
  return !1;
}
function Ae(e) {
  function t(n, r, l, i, o) {
    this._reactName = n, this._targetInst = l, this.type = r, this.nativeEvent = i, this.target = o, this.currentTarget = null;
    for (var u in e) e.hasOwnProperty(u) && (n = e[u], this[u] = n ? n(i) : i[u]);
    return this.isDefaultPrevented = (i.defaultPrevented != null ? i.defaultPrevented : i.returnValue === !1) ? Rr : Fu, this.isPropagationStopped = Fu, this;
  }
  return q(t.prototype, { preventDefault: function() {
    this.defaultPrevented = !0;
    var n = this.nativeEvent;
    n && (n.preventDefault ? n.preventDefault() : typeof n.returnValue != "unknown" && (n.returnValue = !1), this.isDefaultPrevented = Rr);
  }, stopPropagation: function() {
    var n = this.nativeEvent;
    n && (n.stopPropagation ? n.stopPropagation() : typeof n.cancelBubble != "unknown" && (n.cancelBubble = !0), this.isPropagationStopped = Rr);
  }, persist: function() {
  }, isPersistent: Rr }), t;
}
var Mn = { eventPhase: 0, bubbles: 0, cancelable: 0, timeStamp: function(e) {
  return e.timeStamp || Date.now();
}, defaultPrevented: 0, isTrusted: 0 }, Fo = Ae(Mn), Er = q({}, Mn, { view: 0, detail: 0 }), bd = Ae(Er), ei, ti, $n, Ml = q({}, Er, { screenX: 0, screenY: 0, clientX: 0, clientY: 0, pageX: 0, pageY: 0, ctrlKey: 0, shiftKey: 0, altKey: 0, metaKey: 0, getModifierState: Wo, button: 0, buttons: 0, relatedTarget: function(e) {
  return e.relatedTarget === void 0 ? e.fromElement === e.srcElement ? e.toElement : e.fromElement : e.relatedTarget;
}, movementX: function(e) {
  return "movementX" in e ? e.movementX : (e !== $n && ($n && e.type === "mousemove" ? (ei = e.screenX - $n.screenX, ti = e.screenY - $n.screenY) : ti = ei = 0, $n = e), ei);
}, movementY: function(e) {
  return "movementY" in e ? e.movementY : ti;
} }), Wu = Ae(Ml), ef = q({}, Ml, { dataTransfer: 0 }), tf = Ae(ef), nf = q({}, Er, { relatedTarget: 0 }), ni = Ae(nf), rf = q({}, Mn, { animationName: 0, elapsedTime: 0, pseudoElement: 0 }), lf = Ae(rf), of = q({}, Mn, { clipboardData: function(e) {
  return "clipboardData" in e ? e.clipboardData : window.clipboardData;
} }), uf = Ae(of), sf = q({}, Mn, { data: 0 }), Ou = Ae(sf), af = {
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
}, cf = {
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
}, df = { Alt: "altKey", Control: "ctrlKey", Meta: "metaKey", Shift: "shiftKey" };
function ff(e) {
  var t = this.nativeEvent;
  return t.getModifierState ? t.getModifierState(e) : (e = df[e]) ? !!t[e] : !1;
}
function Wo() {
  return ff;
}
var pf = q({}, Er, { key: function(e) {
  if (e.key) {
    var t = af[e.key] || e.key;
    if (t !== "Unidentified") return t;
  }
  return e.type === "keypress" ? (e = Jr(e), e === 13 ? "Enter" : String.fromCharCode(e)) : e.type === "keydown" || e.type === "keyup" ? cf[e.keyCode] || "Unidentified" : "";
}, code: 0, location: 0, ctrlKey: 0, shiftKey: 0, altKey: 0, metaKey: 0, repeat: 0, locale: 0, getModifierState: Wo, charCode: function(e) {
  return e.type === "keypress" ? Jr(e) : 0;
}, keyCode: function(e) {
  return e.type === "keydown" || e.type === "keyup" ? e.keyCode : 0;
}, which: function(e) {
  return e.type === "keypress" ? Jr(e) : e.type === "keydown" || e.type === "keyup" ? e.keyCode : 0;
} }), mf = Ae(pf), hf = q({}, Ml, { pointerId: 0, width: 0, height: 0, pressure: 0, tangentialPressure: 0, tiltX: 0, tiltY: 0, twist: 0, pointerType: 0, isPrimary: 0 }), Ru = Ae(hf), vf = q({}, Er, { touches: 0, targetTouches: 0, changedTouches: 0, altKey: 0, metaKey: 0, ctrlKey: 0, shiftKey: 0, getModifierState: Wo }), gf = Ae(vf), yf = q({}, Mn, { propertyName: 0, elapsedTime: 0, pseudoElement: 0 }), wf = Ae(yf), kf = q({}, Ml, {
  deltaX: function(e) {
    return "deltaX" in e ? e.deltaX : "wheelDeltaX" in e ? -e.wheelDeltaX : 0;
  },
  deltaY: function(e) {
    return "deltaY" in e ? e.deltaY : "wheelDeltaY" in e ? -e.wheelDeltaY : "wheelDelta" in e ? -e.wheelDelta : 0;
  },
  deltaZ: 0,
  deltaMode: 0
}), Sf = Ae(kf), _f = [9, 13, 27, 32], Oo = ft && "CompositionEvent" in window, bn = null;
ft && "documentMode" in document && (bn = document.documentMode);
var xf = ft && "TextEvent" in window && !bn, Sa = ft && (!Oo || bn && 8 < bn && 11 >= bn), Au = " ", Iu = !1;
function _a(e, t) {
  switch (e) {
    case "keyup":
      return _f.indexOf(t.keyCode) !== -1;
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
function xa(e) {
  return e = e.detail, typeof e == "object" && "data" in e ? e.data : null;
}
var sn = !1;
function zf(e, t) {
  switch (e) {
    case "compositionend":
      return xa(t);
    case "keypress":
      return t.which !== 32 ? null : (Iu = !0, Au);
    case "textInput":
      return e = t.data, e === Au && Iu ? null : e;
    default:
      return null;
  }
}
function Ef(e, t) {
  if (sn) return e === "compositionend" || !Oo && _a(e, t) ? (e = ka(), Zr = Mo = xt = null, sn = !1, e) : null;
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
      return Sa && t.locale !== "ko" ? null : t.data;
    default:
      return null;
  }
}
var Nf = { color: !0, date: !0, datetime: !0, "datetime-local": !0, email: !0, month: !0, number: !0, password: !0, range: !0, search: !0, tel: !0, text: !0, time: !0, url: !0, week: !0 };
function Du(e) {
  var t = e && e.nodeName && e.nodeName.toLowerCase();
  return t === "input" ? !!Nf[e.type] : t === "textarea";
}
function za(e, t, n, r) {
  ta(r), t = pl(t, "onChange"), 0 < t.length && (n = new Fo("onChange", "change", null, n, r), e.push({ event: n, listeners: t }));
}
var er = null, fr = null;
function Cf(e) {
  Oa(e, 0);
}
function Fl(e) {
  var t = dn(e);
  if (Ys(t)) return e;
}
function jf(e, t) {
  if (e === "change") return t;
}
var Ea = !1;
if (ft) {
  var ri;
  if (ft) {
    var li = "oninput" in document;
    if (!li) {
      var Uu = document.createElement("div");
      Uu.setAttribute("oninput", "return;"), li = typeof Uu.oninput == "function";
    }
    ri = li;
  } else ri = !1;
  Ea = ri && (!document.documentMode || 9 < document.documentMode);
}
function $u() {
  er && (er.detachEvent("onpropertychange", Na), fr = er = null);
}
function Na(e) {
  if (e.propertyName === "value" && Fl(fr)) {
    var t = [];
    za(t, fr, e, Co(e)), ia(Cf, t);
  }
}
function Tf(e, t, n) {
  e === "focusin" ? ($u(), er = t, fr = n, er.attachEvent("onpropertychange", Na)) : e === "focusout" && $u();
}
function Pf(e) {
  if (e === "selectionchange" || e === "keyup" || e === "keydown") return Fl(fr);
}
function Lf(e, t) {
  if (e === "click") return Fl(t);
}
function Mf(e, t) {
  if (e === "input" || e === "change") return Fl(t);
}
function Ff(e, t) {
  return e === t && (e !== 0 || 1 / e === 1 / t) || e !== e && t !== t;
}
var qe = typeof Object.is == "function" ? Object.is : Ff;
function pr(e, t) {
  if (qe(e, t)) return !0;
  if (typeof e != "object" || e === null || typeof t != "object" || t === null) return !1;
  var n = Object.keys(e), r = Object.keys(t);
  if (n.length !== r.length) return !1;
  for (r = 0; r < n.length; r++) {
    var l = n[r];
    if (!xi.call(t, l) || !qe(e[l], t[l])) return !1;
  }
  return !0;
}
function Vu(e) {
  for (; e && e.firstChild; ) e = e.firstChild;
  return e;
}
function Bu(e, t) {
  var n = Vu(e);
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
    n = Vu(n);
  }
}
function Ca(e, t) {
  return e && t ? e === t ? !0 : e && e.nodeType === 3 ? !1 : t && t.nodeType === 3 ? Ca(e, t.parentNode) : "contains" in e ? e.contains(t) : e.compareDocumentPosition ? !!(e.compareDocumentPosition(t) & 16) : !1 : !1;
}
function ja() {
  for (var e = window, t = ol(); t instanceof e.HTMLIFrameElement; ) {
    try {
      var n = typeof t.contentWindow.location.href == "string";
    } catch {
      n = !1;
    }
    if (n) e = t.contentWindow;
    else break;
    t = ol(e.document);
  }
  return t;
}
function Ro(e) {
  var t = e && e.nodeName && e.nodeName.toLowerCase();
  return t && (t === "input" && (e.type === "text" || e.type === "search" || e.type === "tel" || e.type === "url" || e.type === "password") || t === "textarea" || e.contentEditable === "true");
}
function Wf(e) {
  var t = ja(), n = e.focusedElem, r = e.selectionRange;
  if (t !== n && n && n.ownerDocument && Ca(n.ownerDocument.documentElement, n)) {
    if (r !== null && Ro(n)) {
      if (t = r.start, e = r.end, e === void 0 && (e = t), "selectionStart" in n) n.selectionStart = t, n.selectionEnd = Math.min(e, n.value.length);
      else if (e = (t = n.ownerDocument || document) && t.defaultView || window, e.getSelection) {
        e = e.getSelection();
        var l = n.textContent.length, i = Math.min(r.start, l);
        r = r.end === void 0 ? i : Math.min(r.end, l), !e.extend && i > r && (l = r, r = i, i = l), l = Bu(n, i);
        var o = Bu(
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
var Of = ft && "documentMode" in document && 11 >= document.documentMode, an = null, Vi = null, tr = null, Bi = !1;
function Ku(e, t, n) {
  var r = n.window === n ? n.document : n.nodeType === 9 ? n : n.ownerDocument;
  Bi || an == null || an !== ol(r) || (r = an, "selectionStart" in r && Ro(r) ? r = { start: r.selectionStart, end: r.selectionEnd } : (r = (r.ownerDocument && r.ownerDocument.defaultView || window).getSelection(), r = { anchorNode: r.anchorNode, anchorOffset: r.anchorOffset, focusNode: r.focusNode, focusOffset: r.focusOffset }), tr && pr(tr, r) || (tr = r, r = pl(Vi, "onSelect"), 0 < r.length && (t = new Fo("onSelect", "select", null, t, n), e.push({ event: t, listeners: r }), t.target = an)));
}
function Ar(e, t) {
  var n = {};
  return n[e.toLowerCase()] = t.toLowerCase(), n["Webkit" + e] = "webkit" + t, n["Moz" + e] = "moz" + t, n;
}
var cn = { animationend: Ar("Animation", "AnimationEnd"), animationiteration: Ar("Animation", "AnimationIteration"), animationstart: Ar("Animation", "AnimationStart"), transitionend: Ar("Transition", "TransitionEnd") }, ii = {}, Ta = {};
ft && (Ta = document.createElement("div").style, "AnimationEvent" in window || (delete cn.animationend.animation, delete cn.animationiteration.animation, delete cn.animationstart.animation), "TransitionEvent" in window || delete cn.transitionend.transition);
function Wl(e) {
  if (ii[e]) return ii[e];
  if (!cn[e]) return e;
  var t = cn[e], n;
  for (n in t) if (t.hasOwnProperty(n) && n in Ta) return ii[e] = t[n];
  return e;
}
var Pa = Wl("animationend"), La = Wl("animationiteration"), Ma = Wl("animationstart"), Fa = Wl("transitionend"), Wa = /* @__PURE__ */ new Map(), Hu = "abort auxClick cancel canPlay canPlayThrough click close contextMenu copy cut drag dragEnd dragEnter dragExit dragLeave dragOver dragStart drop durationChange emptied encrypted ended error gotPointerCapture input invalid keyDown keyPress keyUp load loadedData loadedMetadata loadStart lostPointerCapture mouseDown mouseMove mouseOut mouseOver mouseUp paste pause play playing pointerCancel pointerDown pointerMove pointerOut pointerOver pointerUp progress rateChange reset resize seeked seeking stalled submit suspend timeUpdate touchCancel touchEnd touchStart volumeChange scroll toggle touchMove waiting wheel".split(" ");
function Ot(e, t) {
  Wa.set(e, t), en(t, [e]);
}
for (var oi = 0; oi < Hu.length; oi++) {
  var ui = Hu[oi], Rf = ui.toLowerCase(), Af = ui[0].toUpperCase() + ui.slice(1);
  Ot(Rf, "on" + Af);
}
Ot(Pa, "onAnimationEnd");
Ot(La, "onAnimationIteration");
Ot(Ma, "onAnimationStart");
Ot("dblclick", "onDoubleClick");
Ot("focusin", "onFocus");
Ot("focusout", "onBlur");
Ot(Fa, "onTransitionEnd");
zn("onMouseEnter", ["mouseout", "mouseover"]);
zn("onMouseLeave", ["mouseout", "mouseover"]);
zn("onPointerEnter", ["pointerout", "pointerover"]);
zn("onPointerLeave", ["pointerout", "pointerover"]);
en("onChange", "change click focusin focusout input keydown keyup selectionchange".split(" "));
en("onSelect", "focusout contextmenu dragend focusin keydown keyup mousedown mouseup selectionchange".split(" "));
en("onBeforeInput", ["compositionend", "keypress", "textInput", "paste"]);
en("onCompositionEnd", "compositionend focusout keydown keypress keyup mousedown".split(" "));
en("onCompositionStart", "compositionstart focusout keydown keypress keyup mousedown".split(" "));
en("onCompositionUpdate", "compositionupdate focusout keydown keypress keyup mousedown".split(" "));
var Xn = "abort canplay canplaythrough durationchange emptied encrypted ended error loadeddata loadedmetadata loadstart pause play playing progress ratechange resize seeked seeking stalled suspend timeupdate volumechange waiting".split(" "), If = new Set("cancel close invalid load scroll toggle".split(" ").concat(Xn));
function Qu(e, t, n) {
  var r = e.type || "unknown-event";
  e.currentTarget = n, Od(r, t, void 0, e), e.currentTarget = null;
}
function Oa(e, t) {
  t = (t & 4) !== 0;
  for (var n = 0; n < e.length; n++) {
    var r = e[n], l = r.event;
    r = r.listeners;
    e: {
      var i = void 0;
      if (t) for (var o = r.length - 1; 0 <= o; o--) {
        var u = r[o], s = u.instance, d = u.currentTarget;
        if (u = u.listener, s !== i && l.isPropagationStopped()) break e;
        Qu(l, u, d), i = s;
      }
      else for (o = 0; o < r.length; o++) {
        if (u = r[o], s = u.instance, d = u.currentTarget, u = u.listener, s !== i && l.isPropagationStopped()) break e;
        Qu(l, u, d), i = s;
      }
    }
  }
  if (sl) throw e = Ii, sl = !1, Ii = null, e;
}
function Q(e, t) {
  var n = t[Yi];
  n === void 0 && (n = t[Yi] = /* @__PURE__ */ new Set());
  var r = e + "__bubble";
  n.has(r) || (Ra(t, e, 2, !1), n.add(r));
}
function si(e, t, n) {
  var r = 0;
  t && (r |= 4), Ra(n, e, r, t);
}
var Ir = "_reactListening" + Math.random().toString(36).slice(2);
function mr(e) {
  if (!e[Ir]) {
    e[Ir] = !0, Bs.forEach(function(n) {
      n !== "selectionchange" && (If.has(n) || si(n, !1, e), si(n, !0, e));
    });
    var t = e.nodeType === 9 ? e : e.ownerDocument;
    t === null || t[Ir] || (t[Ir] = !0, si("selectionchange", !1, t));
  }
}
function Ra(e, t, n, r) {
  switch (wa(t)) {
    case 1:
      var l = Jd;
      break;
    case 4:
      l = qd;
      break;
    default:
      l = Lo;
  }
  n = l.bind(null, t, n, e), l = void 0, !Ai || t !== "touchstart" && t !== "touchmove" && t !== "wheel" || (l = !0), r ? l !== void 0 ? e.addEventListener(t, n, { capture: !0, passive: l }) : e.addEventListener(t, n, !0) : l !== void 0 ? e.addEventListener(t, n, { passive: l }) : e.addEventListener(t, n, !1);
}
function ai(e, t, n, r, l) {
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
        if (o = Kt(u), o === null) return;
        if (s = o.tag, s === 5 || s === 6) {
          r = i = o;
          continue e;
        }
        u = u.parentNode;
      }
    }
    r = r.return;
  }
  ia(function() {
    var d = i, m = Co(n), v = [];
    e: {
      var h = Wa.get(e);
      if (h !== void 0) {
        var _ = Fo, x = e;
        switch (e) {
          case "keypress":
            if (Jr(n) === 0) break e;
          case "keydown":
          case "keyup":
            _ = mf;
            break;
          case "focusin":
            x = "focus", _ = ni;
            break;
          case "focusout":
            x = "blur", _ = ni;
            break;
          case "beforeblur":
          case "afterblur":
            _ = ni;
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
            _ = Wu;
            break;
          case "drag":
          case "dragend":
          case "dragenter":
          case "dragexit":
          case "dragleave":
          case "dragover":
          case "dragstart":
          case "drop":
            _ = tf;
            break;
          case "touchcancel":
          case "touchend":
          case "touchmove":
          case "touchstart":
            _ = gf;
            break;
          case Pa:
          case La:
          case Ma:
            _ = lf;
            break;
          case Fa:
            _ = wf;
            break;
          case "scroll":
            _ = bd;
            break;
          case "wheel":
            _ = Sf;
            break;
          case "copy":
          case "cut":
          case "paste":
            _ = uf;
            break;
          case "gotpointercapture":
          case "lostpointercapture":
          case "pointercancel":
          case "pointerdown":
          case "pointermove":
          case "pointerout":
          case "pointerover":
          case "pointerup":
            _ = Ru;
        }
        var k = (t & 4) !== 0, K = !k && e === "scroll", f = k ? h !== null ? h + "Capture" : null : h;
        k = [];
        for (var c = d, p; c !== null; ) {
          p = c;
          var y = p.stateNode;
          if (p.tag === 5 && y !== null && (p = y, f !== null && (y = sr(c, f), y != null && k.push(hr(c, y, p)))), K) break;
          c = c.return;
        }
        0 < k.length && (h = new _(h, x, null, n, m), v.push({ event: h, listeners: k }));
      }
    }
    if (!(t & 7)) {
      e: {
        if (h = e === "mouseover" || e === "pointerover", _ = e === "mouseout" || e === "pointerout", h && n !== Oi && (x = n.relatedTarget || n.fromElement) && (Kt(x) || x[pt])) break e;
        if ((_ || h) && (h = m.window === m ? m : (h = m.ownerDocument) ? h.defaultView || h.parentWindow : window, _ ? (x = n.relatedTarget || n.toElement, _ = d, x = x ? Kt(x) : null, x !== null && (K = tn(x), x !== K || x.tag !== 5 && x.tag !== 6) && (x = null)) : (_ = null, x = d), _ !== x)) {
          if (k = Wu, y = "onMouseLeave", f = "onMouseEnter", c = "mouse", (e === "pointerout" || e === "pointerover") && (k = Ru, y = "onPointerLeave", f = "onPointerEnter", c = "pointer"), K = _ == null ? h : dn(_), p = x == null ? h : dn(x), h = new k(y, c + "leave", _, n, m), h.target = K, h.relatedTarget = p, y = null, Kt(m) === d && (k = new k(f, c + "enter", x, n, m), k.target = p, k.relatedTarget = K, y = k), K = y, _ && x) t: {
            for (k = _, f = x, c = 0, p = k; p; p = rn(p)) c++;
            for (p = 0, y = f; y; y = rn(y)) p++;
            for (; 0 < c - p; ) k = rn(k), c--;
            for (; 0 < p - c; ) f = rn(f), p--;
            for (; c--; ) {
              if (k === f || f !== null && k === f.alternate) break t;
              k = rn(k), f = rn(f);
            }
            k = null;
          }
          else k = null;
          _ !== null && Gu(v, h, _, k, !1), x !== null && K !== null && Gu(v, K, x, k, !0);
        }
      }
      e: {
        if (h = d ? dn(d) : window, _ = h.nodeName && h.nodeName.toLowerCase(), _ === "select" || _ === "input" && h.type === "file") var E = jf;
        else if (Du(h)) if (Ea) E = Mf;
        else {
          E = Pf;
          var z = Tf;
        }
        else (_ = h.nodeName) && _.toLowerCase() === "input" && (h.type === "checkbox" || h.type === "radio") && (E = Lf);
        if (E && (E = E(e, d))) {
          za(v, E, n, m);
          break e;
        }
        z && z(e, h, d), e === "focusout" && (z = h._wrapperState) && z.controlled && h.type === "number" && Pi(h, "number", h.value);
      }
      switch (z = d ? dn(d) : window, e) {
        case "focusin":
          (Du(z) || z.contentEditable === "true") && (an = z, Vi = d, tr = null);
          break;
        case "focusout":
          tr = Vi = an = null;
          break;
        case "mousedown":
          Bi = !0;
          break;
        case "contextmenu":
        case "mouseup":
        case "dragend":
          Bi = !1, Ku(v, n, m);
          break;
        case "selectionchange":
          if (Of) break;
        case "keydown":
        case "keyup":
          Ku(v, n, m);
      }
      var T;
      if (Oo) e: {
        switch (e) {
          case "compositionstart":
            var P = "onCompositionStart";
            break e;
          case "compositionend":
            P = "onCompositionEnd";
            break e;
          case "compositionupdate":
            P = "onCompositionUpdate";
            break e;
        }
        P = void 0;
      }
      else sn ? _a(e, n) && (P = "onCompositionEnd") : e === "keydown" && n.keyCode === 229 && (P = "onCompositionStart");
      P && (Sa && n.locale !== "ko" && (sn || P !== "onCompositionStart" ? P === "onCompositionEnd" && sn && (T = ka()) : (xt = m, Mo = "value" in xt ? xt.value : xt.textContent, sn = !0)), z = pl(d, P), 0 < z.length && (P = new Ou(P, e, null, n, m), v.push({ event: P, listeners: z }), T ? P.data = T : (T = xa(n), T !== null && (P.data = T)))), (T = xf ? zf(e, n) : Ef(e, n)) && (d = pl(d, "onBeforeInput"), 0 < d.length && (m = new Ou("onBeforeInput", "beforeinput", null, n, m), v.push({ event: m, listeners: d }), m.data = T));
    }
    Oa(v, t);
  });
}
function hr(e, t, n) {
  return { instance: e, listener: t, currentTarget: n };
}
function pl(e, t) {
  for (var n = t + "Capture", r = []; e !== null; ) {
    var l = e, i = l.stateNode;
    l.tag === 5 && i !== null && (l = i, i = sr(e, n), i != null && r.unshift(hr(e, i, l)), i = sr(e, t), i != null && r.push(hr(e, i, l))), e = e.return;
  }
  return r;
}
function rn(e) {
  if (e === null) return null;
  do
    e = e.return;
  while (e && e.tag !== 5);
  return e || null;
}
function Gu(e, t, n, r, l) {
  for (var i = t._reactName, o = []; n !== null && n !== r; ) {
    var u = n, s = u.alternate, d = u.stateNode;
    if (s !== null && s === r) break;
    u.tag === 5 && d !== null && (u = d, l ? (s = sr(n, i), s != null && o.unshift(hr(n, s, u))) : l || (s = sr(n, i), s != null && o.push(hr(n, s, u)))), n = n.return;
  }
  o.length !== 0 && e.push({ event: t, listeners: o });
}
var Df = /\r\n?/g, Uf = /\u0000|\uFFFD/g;
function Yu(e) {
  return (typeof e == "string" ? e : "" + e).replace(Df, `
`).replace(Uf, "");
}
function Dr(e, t, n) {
  if (t = Yu(t), Yu(e) !== t && n) throw Error(w(425));
}
function ml() {
}
var Ki = null, Hi = null;
function Qi(e, t) {
  return e === "textarea" || e === "noscript" || typeof t.children == "string" || typeof t.children == "number" || typeof t.dangerouslySetInnerHTML == "object" && t.dangerouslySetInnerHTML !== null && t.dangerouslySetInnerHTML.__html != null;
}
var Gi = typeof setTimeout == "function" ? setTimeout : void 0, $f = typeof clearTimeout == "function" ? clearTimeout : void 0, Xu = typeof Promise == "function" ? Promise : void 0, Vf = typeof queueMicrotask == "function" ? queueMicrotask : typeof Xu < "u" ? function(e) {
  return Xu.resolve(null).then(e).catch(Bf);
} : Gi;
function Bf(e) {
  setTimeout(function() {
    throw e;
  });
}
function ci(e, t) {
  var n = t, r = 0;
  do {
    var l = n.nextSibling;
    if (e.removeChild(n), l && l.nodeType === 8) if (n = l.data, n === "/$") {
      if (r === 0) {
        e.removeChild(l), dr(t);
        return;
      }
      r--;
    } else n !== "$" && n !== "$?" && n !== "$!" || r++;
    n = l;
  } while (n);
  dr(t);
}
function jt(e) {
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
function Zu(e) {
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
var Fn = Math.random().toString(36).slice(2), tt = "__reactFiber$" + Fn, vr = "__reactProps$" + Fn, pt = "__reactContainer$" + Fn, Yi = "__reactEvents$" + Fn, Kf = "__reactListeners$" + Fn, Hf = "__reactHandles$" + Fn;
function Kt(e) {
  var t = e[tt];
  if (t) return t;
  for (var n = e.parentNode; n; ) {
    if (t = n[pt] || n[tt]) {
      if (n = t.alternate, t.child !== null || n !== null && n.child !== null) for (e = Zu(e); e !== null; ) {
        if (n = e[tt]) return n;
        e = Zu(e);
      }
      return t;
    }
    e = n, n = e.parentNode;
  }
  return null;
}
function Nr(e) {
  return e = e[tt] || e[pt], !e || e.tag !== 5 && e.tag !== 6 && e.tag !== 13 && e.tag !== 3 ? null : e;
}
function dn(e) {
  if (e.tag === 5 || e.tag === 6) return e.stateNode;
  throw Error(w(33));
}
function Ol(e) {
  return e[vr] || null;
}
var Xi = [], fn = -1;
function Rt(e) {
  return { current: e };
}
function G(e) {
  0 > fn || (e.current = Xi[fn], Xi[fn] = null, fn--);
}
function H(e, t) {
  fn++, Xi[fn] = e.current, e.current = t;
}
var Wt = {}, we = Rt(Wt), Ce = Rt(!1), Xt = Wt;
function En(e, t) {
  var n = e.type.contextTypes;
  if (!n) return Wt;
  var r = e.stateNode;
  if (r && r.__reactInternalMemoizedUnmaskedChildContext === t) return r.__reactInternalMemoizedMaskedChildContext;
  var l = {}, i;
  for (i in n) l[i] = t[i];
  return r && (e = e.stateNode, e.__reactInternalMemoizedUnmaskedChildContext = t, e.__reactInternalMemoizedMaskedChildContext = l), l;
}
function je(e) {
  return e = e.childContextTypes, e != null;
}
function hl() {
  G(Ce), G(we);
}
function Ju(e, t, n) {
  if (we.current !== Wt) throw Error(w(168));
  H(we, t), H(Ce, n);
}
function Aa(e, t, n) {
  var r = e.stateNode;
  if (t = t.childContextTypes, typeof r.getChildContext != "function") return n;
  r = r.getChildContext();
  for (var l in r) if (!(l in t)) throw Error(w(108, jd(e) || "Unknown", l));
  return q({}, n, r);
}
function vl(e) {
  return e = (e = e.stateNode) && e.__reactInternalMemoizedMergedChildContext || Wt, Xt = we.current, H(we, e), H(Ce, Ce.current), !0;
}
function qu(e, t, n) {
  var r = e.stateNode;
  if (!r) throw Error(w(169));
  n ? (e = Aa(e, t, Xt), r.__reactInternalMemoizedMergedChildContext = e, G(Ce), G(we), H(we, e)) : G(Ce), H(Ce, n);
}
var st = null, Rl = !1, di = !1;
function Ia(e) {
  st === null ? st = [e] : st.push(e);
}
function Qf(e) {
  Rl = !0, Ia(e);
}
function At() {
  if (!di && st !== null) {
    di = !0;
    var e = 0, t = $;
    try {
      var n = st;
      for ($ = 1; e < n.length; e++) {
        var r = n[e];
        do
          r = r(!0);
        while (r !== null);
      }
      st = null, Rl = !1;
    } catch (l) {
      throw st !== null && (st = st.slice(e + 1)), aa(jo, At), l;
    } finally {
      $ = t, di = !1;
    }
  }
  return null;
}
var pn = [], mn = 0, gl = null, yl = 0, Ue = [], $e = 0, Zt = null, at = 1, ct = "";
function Vt(e, t) {
  pn[mn++] = yl, pn[mn++] = gl, gl = e, yl = t;
}
function Da(e, t, n) {
  Ue[$e++] = at, Ue[$e++] = ct, Ue[$e++] = Zt, Zt = e;
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
function Ao(e) {
  e.return !== null && (Vt(e, 1), Da(e, 1, 0));
}
function Io(e) {
  for (; e === gl; ) gl = pn[--mn], pn[mn] = null, yl = pn[--mn], pn[mn] = null;
  for (; e === Zt; ) Zt = Ue[--$e], Ue[$e] = null, ct = Ue[--$e], Ue[$e] = null, at = Ue[--$e], Ue[$e] = null;
}
var We = null, Me = null, Y = !1, Xe = null;
function Ua(e, t) {
  var n = Ve(5, null, null, 0);
  n.elementType = "DELETED", n.stateNode = t, n.return = e, t = e.deletions, t === null ? (e.deletions = [n], e.flags |= 16) : t.push(n);
}
function bu(e, t) {
  switch (e.tag) {
    case 5:
      var n = e.type;
      return t = t.nodeType !== 1 || n.toLowerCase() !== t.nodeName.toLowerCase() ? null : t, t !== null ? (e.stateNode = t, We = e, Me = jt(t.firstChild), !0) : !1;
    case 6:
      return t = e.pendingProps === "" || t.nodeType !== 3 ? null : t, t !== null ? (e.stateNode = t, We = e, Me = null, !0) : !1;
    case 13:
      return t = t.nodeType !== 8 ? null : t, t !== null ? (n = Zt !== null ? { id: at, overflow: ct } : null, e.memoizedState = { dehydrated: t, treeContext: n, retryLane: 1073741824 }, n = Ve(18, null, null, 0), n.stateNode = t, n.return = e, e.child = n, We = e, Me = null, !0) : !1;
    default:
      return !1;
  }
}
function Zi(e) {
  return (e.mode & 1) !== 0 && (e.flags & 128) === 0;
}
function Ji(e) {
  if (Y) {
    var t = Me;
    if (t) {
      var n = t;
      if (!bu(e, t)) {
        if (Zi(e)) throw Error(w(418));
        t = jt(n.nextSibling);
        var r = We;
        t && bu(e, t) ? Ua(r, n) : (e.flags = e.flags & -4097 | 2, Y = !1, We = e);
      }
    } else {
      if (Zi(e)) throw Error(w(418));
      e.flags = e.flags & -4097 | 2, Y = !1, We = e;
    }
  }
}
function es(e) {
  for (e = e.return; e !== null && e.tag !== 5 && e.tag !== 3 && e.tag !== 13; ) e = e.return;
  We = e;
}
function Ur(e) {
  if (e !== We) return !1;
  if (!Y) return es(e), Y = !0, !1;
  var t;
  if ((t = e.tag !== 3) && !(t = e.tag !== 5) && (t = e.type, t = t !== "head" && t !== "body" && !Qi(e.type, e.memoizedProps)), t && (t = Me)) {
    if (Zi(e)) throw $a(), Error(w(418));
    for (; t; ) Ua(e, t), t = jt(t.nextSibling);
  }
  if (es(e), e.tag === 13) {
    if (e = e.memoizedState, e = e !== null ? e.dehydrated : null, !e) throw Error(w(317));
    e: {
      for (e = e.nextSibling, t = 0; e; ) {
        if (e.nodeType === 8) {
          var n = e.data;
          if (n === "/$") {
            if (t === 0) {
              Me = jt(e.nextSibling);
              break e;
            }
            t--;
          } else n !== "$" && n !== "$!" && n !== "$?" || t++;
        }
        e = e.nextSibling;
      }
      Me = null;
    }
  } else Me = We ? jt(e.stateNode.nextSibling) : null;
  return !0;
}
function $a() {
  for (var e = Me; e; ) e = jt(e.nextSibling);
}
function Nn() {
  Me = We = null, Y = !1;
}
function Do(e) {
  Xe === null ? Xe = [e] : Xe.push(e);
}
var Gf = vt.ReactCurrentBatchConfig;
function Vn(e, t, n) {
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
function $r(e, t) {
  throw e = Object.prototype.toString.call(t), Error(w(31, e === "[object Object]" ? "object with keys {" + Object.keys(t).join(", ") + "}" : e));
}
function ts(e) {
  var t = e._init;
  return t(e._payload);
}
function Va(e) {
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
    return c === null || c.tag !== 6 ? (c = yi(p, f.mode, y), c.return = f, c) : (c = l(c, p), c.return = f, c);
  }
  function s(f, c, p, y) {
    var E = p.type;
    return E === un ? m(f, c, p.props.children, y, p.key) : c !== null && (c.elementType === E || typeof E == "object" && E !== null && E.$$typeof === wt && ts(E) === c.type) ? (y = l(c, p.props), y.ref = Vn(f, c, p), y.return = f, y) : (y = ll(p.type, p.key, p.props, null, f.mode, y), y.ref = Vn(f, c, p), y.return = f, y);
  }
  function d(f, c, p, y) {
    return c === null || c.tag !== 4 || c.stateNode.containerInfo !== p.containerInfo || c.stateNode.implementation !== p.implementation ? (c = wi(p, f.mode, y), c.return = f, c) : (c = l(c, p.children || []), c.return = f, c);
  }
  function m(f, c, p, y, E) {
    return c === null || c.tag !== 7 ? (c = Yt(p, f.mode, y, E), c.return = f, c) : (c = l(c, p), c.return = f, c);
  }
  function v(f, c, p) {
    if (typeof c == "string" && c !== "" || typeof c == "number") return c = yi("" + c, f.mode, p), c.return = f, c;
    if (typeof c == "object" && c !== null) {
      switch (c.$$typeof) {
        case Pr:
          return p = ll(c.type, c.key, c.props, null, f.mode, p), p.ref = Vn(f, null, c), p.return = f, p;
        case on:
          return c = wi(c, f.mode, p), c.return = f, c;
        case wt:
          var y = c._init;
          return v(f, y(c._payload), p);
      }
      if (Gn(c) || An(c)) return c = Yt(c, f.mode, p, null), c.return = f, c;
      $r(f, c);
    }
    return null;
  }
  function h(f, c, p, y) {
    var E = c !== null ? c.key : null;
    if (typeof p == "string" && p !== "" || typeof p == "number") return E !== null ? null : u(f, c, "" + p, y);
    if (typeof p == "object" && p !== null) {
      switch (p.$$typeof) {
        case Pr:
          return p.key === E ? s(f, c, p, y) : null;
        case on:
          return p.key === E ? d(f, c, p, y) : null;
        case wt:
          return E = p._init, h(
            f,
            c,
            E(p._payload),
            y
          );
      }
      if (Gn(p) || An(p)) return E !== null ? null : m(f, c, p, y, null);
      $r(f, p);
    }
    return null;
  }
  function _(f, c, p, y, E) {
    if (typeof y == "string" && y !== "" || typeof y == "number") return f = f.get(p) || null, u(c, f, "" + y, E);
    if (typeof y == "object" && y !== null) {
      switch (y.$$typeof) {
        case Pr:
          return f = f.get(y.key === null ? p : y.key) || null, s(c, f, y, E);
        case on:
          return f = f.get(y.key === null ? p : y.key) || null, d(c, f, y, E);
        case wt:
          var z = y._init;
          return _(f, c, p, z(y._payload), E);
      }
      if (Gn(y) || An(y)) return f = f.get(p) || null, m(c, f, y, E, null);
      $r(c, y);
    }
    return null;
  }
  function x(f, c, p, y) {
    for (var E = null, z = null, T = c, P = c = 0, V = null; T !== null && P < p.length; P++) {
      T.index > P ? (V = T, T = null) : V = T.sibling;
      var R = h(f, T, p[P], y);
      if (R === null) {
        T === null && (T = V);
        break;
      }
      e && T && R.alternate === null && t(f, T), c = i(R, c, P), z === null ? E = R : z.sibling = R, z = R, T = V;
    }
    if (P === p.length) return n(f, T), Y && Vt(f, P), E;
    if (T === null) {
      for (; P < p.length; P++) T = v(f, p[P], y), T !== null && (c = i(T, c, P), z === null ? E = T : z.sibling = T, z = T);
      return Y && Vt(f, P), E;
    }
    for (T = r(f, T); P < p.length; P++) V = _(T, f, P, p[P], y), V !== null && (e && V.alternate !== null && T.delete(V.key === null ? P : V.key), c = i(V, c, P), z === null ? E = V : z.sibling = V, z = V);
    return e && T.forEach(function(se) {
      return t(f, se);
    }), Y && Vt(f, P), E;
  }
  function k(f, c, p, y) {
    var E = An(p);
    if (typeof E != "function") throw Error(w(150));
    if (p = E.call(p), p == null) throw Error(w(151));
    for (var z = E = null, T = c, P = c = 0, V = null, R = p.next(); T !== null && !R.done; P++, R = p.next()) {
      T.index > P ? (V = T, T = null) : V = T.sibling;
      var se = h(f, T, R.value, y);
      if (se === null) {
        T === null && (T = V);
        break;
      }
      e && T && se.alternate === null && t(f, T), c = i(se, c, P), z === null ? E = se : z.sibling = se, z = se, T = V;
    }
    if (R.done) return n(
      f,
      T
    ), Y && Vt(f, P), E;
    if (T === null) {
      for (; !R.done; P++, R = p.next()) R = v(f, R.value, y), R !== null && (c = i(R, c, P), z === null ? E = R : z.sibling = R, z = R);
      return Y && Vt(f, P), E;
    }
    for (T = r(f, T); !R.done; P++, R = p.next()) R = _(T, f, P, R.value, y), R !== null && (e && R.alternate !== null && T.delete(R.key === null ? P : R.key), c = i(R, c, P), z === null ? E = R : z.sibling = R, z = R);
    return e && T.forEach(function(It) {
      return t(f, It);
    }), Y && Vt(f, P), E;
  }
  function K(f, c, p, y) {
    if (typeof p == "object" && p !== null && p.type === un && p.key === null && (p = p.props.children), typeof p == "object" && p !== null) {
      switch (p.$$typeof) {
        case Pr:
          e: {
            for (var E = p.key, z = c; z !== null; ) {
              if (z.key === E) {
                if (E = p.type, E === un) {
                  if (z.tag === 7) {
                    n(f, z.sibling), c = l(z, p.props.children), c.return = f, f = c;
                    break e;
                  }
                } else if (z.elementType === E || typeof E == "object" && E !== null && E.$$typeof === wt && ts(E) === z.type) {
                  n(f, z.sibling), c = l(z, p.props), c.ref = Vn(f, z, p), c.return = f, f = c;
                  break e;
                }
                n(f, z);
                break;
              } else t(f, z);
              z = z.sibling;
            }
            p.type === un ? (c = Yt(p.props.children, f.mode, y, p.key), c.return = f, f = c) : (y = ll(p.type, p.key, p.props, null, f.mode, y), y.ref = Vn(f, c, p), y.return = f, f = y);
          }
          return o(f);
        case on:
          e: {
            for (z = p.key; c !== null; ) {
              if (c.key === z) if (c.tag === 4 && c.stateNode.containerInfo === p.containerInfo && c.stateNode.implementation === p.implementation) {
                n(f, c.sibling), c = l(c, p.children || []), c.return = f, f = c;
                break e;
              } else {
                n(f, c);
                break;
              }
              else t(f, c);
              c = c.sibling;
            }
            c = wi(p, f.mode, y), c.return = f, f = c;
          }
          return o(f);
        case wt:
          return z = p._init, K(f, c, z(p._payload), y);
      }
      if (Gn(p)) return x(f, c, p, y);
      if (An(p)) return k(f, c, p, y);
      $r(f, p);
    }
    return typeof p == "string" && p !== "" || typeof p == "number" ? (p = "" + p, c !== null && c.tag === 6 ? (n(f, c.sibling), c = l(c, p), c.return = f, f = c) : (n(f, c), c = yi(p, f.mode, y), c.return = f, f = c), o(f)) : n(f, c);
  }
  return K;
}
var Cn = Va(!0), Ba = Va(!1), wl = Rt(null), kl = null, hn = null, Uo = null;
function $o() {
  Uo = hn = kl = null;
}
function Vo(e) {
  var t = wl.current;
  G(wl), e._currentValue = t;
}
function qi(e, t, n) {
  for (; e !== null; ) {
    var r = e.alternate;
    if ((e.childLanes & t) !== t ? (e.childLanes |= t, r !== null && (r.childLanes |= t)) : r !== null && (r.childLanes & t) !== t && (r.childLanes |= t), e === n) break;
    e = e.return;
  }
}
function _n(e, t) {
  kl = e, Uo = hn = null, e = e.dependencies, e !== null && e.firstContext !== null && (e.lanes & t && (Ne = !0), e.firstContext = null);
}
function Ke(e) {
  var t = e._currentValue;
  if (Uo !== e) if (e = { context: e, memoizedValue: t, next: null }, hn === null) {
    if (kl === null) throw Error(w(308));
    hn = e, kl.dependencies = { lanes: 0, firstContext: e };
  } else hn = hn.next = e;
  return t;
}
var Ht = null;
function Bo(e) {
  Ht === null ? Ht = [e] : Ht.push(e);
}
function Ka(e, t, n, r) {
  var l = t.interleaved;
  return l === null ? (n.next = n, Bo(t)) : (n.next = l.next, l.next = n), t.interleaved = n, mt(e, r);
}
function mt(e, t) {
  e.lanes |= t;
  var n = e.alternate;
  for (n !== null && (n.lanes |= t), n = e, e = e.return; e !== null; ) e.childLanes |= t, n = e.alternate, n !== null && (n.childLanes |= t), n = e, e = e.return;
  return n.tag === 3 ? n.stateNode : null;
}
var kt = !1;
function Ko(e) {
  e.updateQueue = { baseState: e.memoizedState, firstBaseUpdate: null, lastBaseUpdate: null, shared: { pending: null, interleaved: null, lanes: 0 }, effects: null };
}
function Ha(e, t) {
  e = e.updateQueue, t.updateQueue === e && (t.updateQueue = { baseState: e.baseState, firstBaseUpdate: e.firstBaseUpdate, lastBaseUpdate: e.lastBaseUpdate, shared: e.shared, effects: e.effects });
}
function dt(e, t) {
  return { eventTime: e, lane: t, tag: 0, payload: null, callback: null, next: null };
}
function Tt(e, t, n) {
  var r = e.updateQueue;
  if (r === null) return null;
  if (r = r.shared, U & 2) {
    var l = r.pending;
    return l === null ? t.next = t : (t.next = l.next, l.next = t), r.pending = t, mt(e, n);
  }
  return l = r.interleaved, l === null ? (t.next = t, Bo(r)) : (t.next = l.next, l.next = t), r.interleaved = t, mt(e, n);
}
function qr(e, t, n) {
  if (t = t.updateQueue, t !== null && (t = t.shared, (n & 4194240) !== 0)) {
    var r = t.lanes;
    r &= e.pendingLanes, n |= r, t.lanes = n, To(e, n);
  }
}
function ns(e, t) {
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
function Sl(e, t, n, r) {
  var l = e.updateQueue;
  kt = !1;
  var i = l.firstBaseUpdate, o = l.lastBaseUpdate, u = l.shared.pending;
  if (u !== null) {
    l.shared.pending = null;
    var s = u, d = s.next;
    s.next = null, o === null ? i = d : o.next = d, o = s;
    var m = e.alternate;
    m !== null && (m = m.updateQueue, u = m.lastBaseUpdate, u !== o && (u === null ? m.firstBaseUpdate = d : u.next = d, m.lastBaseUpdate = s));
  }
  if (i !== null) {
    var v = l.baseState;
    o = 0, m = d = s = null, u = i;
    do {
      var h = u.lane, _ = u.eventTime;
      if ((r & h) === h) {
        m !== null && (m = m.next = {
          eventTime: _,
          lane: 0,
          tag: u.tag,
          payload: u.payload,
          callback: u.callback,
          next: null
        });
        e: {
          var x = e, k = u;
          switch (h = t, _ = n, k.tag) {
            case 1:
              if (x = k.payload, typeof x == "function") {
                v = x.call(_, v, h);
                break e;
              }
              v = x;
              break e;
            case 3:
              x.flags = x.flags & -65537 | 128;
            case 0:
              if (x = k.payload, h = typeof x == "function" ? x.call(_, v, h) : x, h == null) break e;
              v = q({}, v, h);
              break e;
            case 2:
              kt = !0;
          }
        }
        u.callback !== null && u.lane !== 0 && (e.flags |= 64, h = l.effects, h === null ? l.effects = [u] : h.push(u));
      } else _ = { eventTime: _, lane: h, tag: u.tag, payload: u.payload, callback: u.callback, next: null }, m === null ? (d = m = _, s = v) : m = m.next = _, o |= h;
      if (u = u.next, u === null) {
        if (u = l.shared.pending, u === null) break;
        h = u, u = h.next, h.next = null, l.lastBaseUpdate = h, l.shared.pending = null;
      }
    } while (!0);
    if (m === null && (s = v), l.baseState = s, l.firstBaseUpdate = d, l.lastBaseUpdate = m, t = l.shared.interleaved, t !== null) {
      l = t;
      do
        o |= l.lane, l = l.next;
      while (l !== t);
    } else i === null && (l.shared.lanes = 0);
    qt |= o, e.lanes = o, e.memoizedState = v;
  }
}
function rs(e, t, n) {
  if (e = t.effects, t.effects = null, e !== null) for (t = 0; t < e.length; t++) {
    var r = e[t], l = r.callback;
    if (l !== null) {
      if (r.callback = null, r = n, typeof l != "function") throw Error(w(191, l));
      l.call(r);
    }
  }
}
var Cr = {}, rt = Rt(Cr), gr = Rt(Cr), yr = Rt(Cr);
function Qt(e) {
  if (e === Cr) throw Error(w(174));
  return e;
}
function Ho(e, t) {
  switch (H(yr, t), H(gr, e), H(rt, Cr), e = t.nodeType, e) {
    case 9:
    case 11:
      t = (t = t.documentElement) ? t.namespaceURI : Mi(null, "");
      break;
    default:
      e = e === 8 ? t.parentNode : t, t = e.namespaceURI || null, e = e.tagName, t = Mi(t, e);
  }
  G(rt), H(rt, t);
}
function jn() {
  G(rt), G(gr), G(yr);
}
function Qa(e) {
  Qt(yr.current);
  var t = Qt(rt.current), n = Mi(t, e.type);
  t !== n && (H(gr, e), H(rt, n));
}
function Qo(e) {
  gr.current === e && (G(rt), G(gr));
}
var Z = Rt(0);
function _l(e) {
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
var fi = [];
function Go() {
  for (var e = 0; e < fi.length; e++) fi[e]._workInProgressVersionPrimary = null;
  fi.length = 0;
}
var br = vt.ReactCurrentDispatcher, pi = vt.ReactCurrentBatchConfig, Jt = 0, J = null, oe = null, de = null, xl = !1, nr = !1, wr = 0, Yf = 0;
function ve() {
  throw Error(w(321));
}
function Yo(e, t) {
  if (t === null) return !1;
  for (var n = 0; n < t.length && n < e.length; n++) if (!qe(e[n], t[n])) return !1;
  return !0;
}
function Xo(e, t, n, r, l, i) {
  if (Jt = i, J = t, t.memoizedState = null, t.updateQueue = null, t.lanes = 0, br.current = e === null || e.memoizedState === null ? qf : bf, e = n(r, l), nr) {
    i = 0;
    do {
      if (nr = !1, wr = 0, 25 <= i) throw Error(w(301));
      i += 1, de = oe = null, t.updateQueue = null, br.current = ep, e = n(r, l);
    } while (nr);
  }
  if (br.current = zl, t = oe !== null && oe.next !== null, Jt = 0, de = oe = J = null, xl = !1, t) throw Error(w(300));
  return e;
}
function Zo() {
  var e = wr !== 0;
  return wr = 0, e;
}
function et() {
  var e = { memoizedState: null, baseState: null, baseQueue: null, queue: null, next: null };
  return de === null ? J.memoizedState = de = e : de = de.next = e, de;
}
function He() {
  if (oe === null) {
    var e = J.alternate;
    e = e !== null ? e.memoizedState : null;
  } else e = oe.next;
  var t = de === null ? J.memoizedState : de.next;
  if (t !== null) de = t, oe = e;
  else {
    if (e === null) throw Error(w(310));
    oe = e, e = { memoizedState: oe.memoizedState, baseState: oe.baseState, baseQueue: oe.baseQueue, queue: oe.queue, next: null }, de === null ? J.memoizedState = de = e : de = de.next = e;
  }
  return de;
}
function kr(e, t) {
  return typeof t == "function" ? t(e) : t;
}
function mi(e) {
  var t = He(), n = t.queue;
  if (n === null) throw Error(w(311));
  n.lastRenderedReducer = e;
  var r = oe, l = r.baseQueue, i = n.pending;
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
      if ((Jt & m) === m) s !== null && (s = s.next = { lane: 0, action: d.action, hasEagerState: d.hasEagerState, eagerState: d.eagerState, next: null }), r = d.hasEagerState ? d.eagerState : e(r, d.action);
      else {
        var v = {
          lane: m,
          action: d.action,
          hasEagerState: d.hasEagerState,
          eagerState: d.eagerState,
          next: null
        };
        s === null ? (u = s = v, o = r) : s = s.next = v, J.lanes |= m, qt |= m;
      }
      d = d.next;
    } while (d !== null && d !== i);
    s === null ? o = r : s.next = u, qe(r, t.memoizedState) || (Ne = !0), t.memoizedState = r, t.baseState = o, t.baseQueue = s, n.lastRenderedState = r;
  }
  if (e = n.interleaved, e !== null) {
    l = e;
    do
      i = l.lane, J.lanes |= i, qt |= i, l = l.next;
    while (l !== e);
  } else l === null && (n.lanes = 0);
  return [t.memoizedState, n.dispatch];
}
function hi(e) {
  var t = He(), n = t.queue;
  if (n === null) throw Error(w(311));
  n.lastRenderedReducer = e;
  var r = n.dispatch, l = n.pending, i = t.memoizedState;
  if (l !== null) {
    n.pending = null;
    var o = l = l.next;
    do
      i = e(i, o.action), o = o.next;
    while (o !== l);
    qe(i, t.memoizedState) || (Ne = !0), t.memoizedState = i, t.baseQueue === null && (t.baseState = i), n.lastRenderedState = i;
  }
  return [i, r];
}
function Ga() {
}
function Ya(e, t) {
  var n = J, r = He(), l = t(), i = !qe(r.memoizedState, l);
  if (i && (r.memoizedState = l, Ne = !0), r = r.queue, Jo(Ja.bind(null, n, r, e), [e]), r.getSnapshot !== t || i || de !== null && de.memoizedState.tag & 1) {
    if (n.flags |= 2048, Sr(9, Za.bind(null, n, r, l, t), void 0, null), fe === null) throw Error(w(349));
    Jt & 30 || Xa(n, t, l);
  }
  return l;
}
function Xa(e, t, n) {
  e.flags |= 16384, e = { getSnapshot: t, value: n }, t = J.updateQueue, t === null ? (t = { lastEffect: null, stores: null }, J.updateQueue = t, t.stores = [e]) : (n = t.stores, n === null ? t.stores = [e] : n.push(e));
}
function Za(e, t, n, r) {
  t.value = n, t.getSnapshot = r, qa(t) && ba(e);
}
function Ja(e, t, n) {
  return n(function() {
    qa(t) && ba(e);
  });
}
function qa(e) {
  var t = e.getSnapshot;
  e = e.value;
  try {
    var n = t();
    return !qe(e, n);
  } catch {
    return !0;
  }
}
function ba(e) {
  var t = mt(e, 1);
  t !== null && Je(t, e, 1, -1);
}
function ls(e) {
  var t = et();
  return typeof e == "function" && (e = e()), t.memoizedState = t.baseState = e, e = { pending: null, interleaved: null, lanes: 0, dispatch: null, lastRenderedReducer: kr, lastRenderedState: e }, t.queue = e, e = e.dispatch = Jf.bind(null, J, e), [t.memoizedState, e];
}
function Sr(e, t, n, r) {
  return e = { tag: e, create: t, destroy: n, deps: r, next: null }, t = J.updateQueue, t === null ? (t = { lastEffect: null, stores: null }, J.updateQueue = t, t.lastEffect = e.next = e) : (n = t.lastEffect, n === null ? t.lastEffect = e.next = e : (r = n.next, n.next = e, e.next = r, t.lastEffect = e)), e;
}
function ec() {
  return He().memoizedState;
}
function el(e, t, n, r) {
  var l = et();
  J.flags |= e, l.memoizedState = Sr(1 | t, n, void 0, r === void 0 ? null : r);
}
function Al(e, t, n, r) {
  var l = He();
  r = r === void 0 ? null : r;
  var i = void 0;
  if (oe !== null) {
    var o = oe.memoizedState;
    if (i = o.destroy, r !== null && Yo(r, o.deps)) {
      l.memoizedState = Sr(t, n, i, r);
      return;
    }
  }
  J.flags |= e, l.memoizedState = Sr(1 | t, n, i, r);
}
function is(e, t) {
  return el(8390656, 8, e, t);
}
function Jo(e, t) {
  return Al(2048, 8, e, t);
}
function tc(e, t) {
  return Al(4, 2, e, t);
}
function nc(e, t) {
  return Al(4, 4, e, t);
}
function rc(e, t) {
  if (typeof t == "function") return e = e(), t(e), function() {
    t(null);
  };
  if (t != null) return e = e(), t.current = e, function() {
    t.current = null;
  };
}
function lc(e, t, n) {
  return n = n != null ? n.concat([e]) : null, Al(4, 4, rc.bind(null, t, e), n);
}
function qo() {
}
function ic(e, t) {
  var n = He();
  t = t === void 0 ? null : t;
  var r = n.memoizedState;
  return r !== null && t !== null && Yo(t, r[1]) ? r[0] : (n.memoizedState = [e, t], e);
}
function oc(e, t) {
  var n = He();
  t = t === void 0 ? null : t;
  var r = n.memoizedState;
  return r !== null && t !== null && Yo(t, r[1]) ? r[0] : (e = e(), n.memoizedState = [e, t], e);
}
function uc(e, t, n) {
  return Jt & 21 ? (qe(n, t) || (n = fa(), J.lanes |= n, qt |= n, e.baseState = !0), t) : (e.baseState && (e.baseState = !1, Ne = !0), e.memoizedState = n);
}
function Xf(e, t) {
  var n = $;
  $ = n !== 0 && 4 > n ? n : 4, e(!0);
  var r = pi.transition;
  pi.transition = {};
  try {
    e(!1), t();
  } finally {
    $ = n, pi.transition = r;
  }
}
function sc() {
  return He().memoizedState;
}
function Zf(e, t, n) {
  var r = Lt(e);
  if (n = { lane: r, action: n, hasEagerState: !1, eagerState: null, next: null }, ac(e)) cc(t, n);
  else if (n = Ka(e, t, n, r), n !== null) {
    var l = Se();
    Je(n, e, r, l), dc(n, t, r);
  }
}
function Jf(e, t, n) {
  var r = Lt(e), l = { lane: r, action: n, hasEagerState: !1, eagerState: null, next: null };
  if (ac(e)) cc(t, l);
  else {
    var i = e.alternate;
    if (e.lanes === 0 && (i === null || i.lanes === 0) && (i = t.lastRenderedReducer, i !== null)) try {
      var o = t.lastRenderedState, u = i(o, n);
      if (l.hasEagerState = !0, l.eagerState = u, qe(u, o)) {
        var s = t.interleaved;
        s === null ? (l.next = l, Bo(t)) : (l.next = s.next, s.next = l), t.interleaved = l;
        return;
      }
    } catch {
    } finally {
    }
    n = Ka(e, t, l, r), n !== null && (l = Se(), Je(n, e, r, l), dc(n, t, r));
  }
}
function ac(e) {
  var t = e.alternate;
  return e === J || t !== null && t === J;
}
function cc(e, t) {
  nr = xl = !0;
  var n = e.pending;
  n === null ? t.next = t : (t.next = n.next, n.next = t), e.pending = t;
}
function dc(e, t, n) {
  if (n & 4194240) {
    var r = t.lanes;
    r &= e.pendingLanes, n |= r, t.lanes = n, To(e, n);
  }
}
var zl = { readContext: Ke, useCallback: ve, useContext: ve, useEffect: ve, useImperativeHandle: ve, useInsertionEffect: ve, useLayoutEffect: ve, useMemo: ve, useReducer: ve, useRef: ve, useState: ve, useDebugValue: ve, useDeferredValue: ve, useTransition: ve, useMutableSource: ve, useSyncExternalStore: ve, useId: ve, unstable_isNewReconciler: !1 }, qf = { readContext: Ke, useCallback: function(e, t) {
  return et().memoizedState = [e, t === void 0 ? null : t], e;
}, useContext: Ke, useEffect: is, useImperativeHandle: function(e, t, n) {
  return n = n != null ? n.concat([e]) : null, el(
    4194308,
    4,
    rc.bind(null, t, e),
    n
  );
}, useLayoutEffect: function(e, t) {
  return el(4194308, 4, e, t);
}, useInsertionEffect: function(e, t) {
  return el(4, 2, e, t);
}, useMemo: function(e, t) {
  var n = et();
  return t = t === void 0 ? null : t, e = e(), n.memoizedState = [e, t], e;
}, useReducer: function(e, t, n) {
  var r = et();
  return t = n !== void 0 ? n(t) : t, r.memoizedState = r.baseState = t, e = { pending: null, interleaved: null, lanes: 0, dispatch: null, lastRenderedReducer: e, lastRenderedState: t }, r.queue = e, e = e.dispatch = Zf.bind(null, J, e), [r.memoizedState, e];
}, useRef: function(e) {
  var t = et();
  return e = { current: e }, t.memoizedState = e;
}, useState: ls, useDebugValue: qo, useDeferredValue: function(e) {
  return et().memoizedState = e;
}, useTransition: function() {
  var e = ls(!1), t = e[0];
  return e = Xf.bind(null, e[1]), et().memoizedState = e, [t, e];
}, useMutableSource: function() {
}, useSyncExternalStore: function(e, t, n) {
  var r = J, l = et();
  if (Y) {
    if (n === void 0) throw Error(w(407));
    n = n();
  } else {
    if (n = t(), fe === null) throw Error(w(349));
    Jt & 30 || Xa(r, t, n);
  }
  l.memoizedState = n;
  var i = { value: n, getSnapshot: t };
  return l.queue = i, is(Ja.bind(
    null,
    r,
    i,
    e
  ), [e]), r.flags |= 2048, Sr(9, Za.bind(null, r, i, n, t), void 0, null), n;
}, useId: function() {
  var e = et(), t = fe.identifierPrefix;
  if (Y) {
    var n = ct, r = at;
    n = (r & ~(1 << 32 - Ze(r) - 1)).toString(32) + n, t = ":" + t + "R" + n, n = wr++, 0 < n && (t += "H" + n.toString(32)), t += ":";
  } else n = Yf++, t = ":" + t + "r" + n.toString(32) + ":";
  return e.memoizedState = t;
}, unstable_isNewReconciler: !1 }, bf = {
  readContext: Ke,
  useCallback: ic,
  useContext: Ke,
  useEffect: Jo,
  useImperativeHandle: lc,
  useInsertionEffect: tc,
  useLayoutEffect: nc,
  useMemo: oc,
  useReducer: mi,
  useRef: ec,
  useState: function() {
    return mi(kr);
  },
  useDebugValue: qo,
  useDeferredValue: function(e) {
    var t = He();
    return uc(t, oe.memoizedState, e);
  },
  useTransition: function() {
    var e = mi(kr)[0], t = He().memoizedState;
    return [e, t];
  },
  useMutableSource: Ga,
  useSyncExternalStore: Ya,
  useId: sc,
  unstable_isNewReconciler: !1
}, ep = { readContext: Ke, useCallback: ic, useContext: Ke, useEffect: Jo, useImperativeHandle: lc, useInsertionEffect: tc, useLayoutEffect: nc, useMemo: oc, useReducer: hi, useRef: ec, useState: function() {
  return hi(kr);
}, useDebugValue: qo, useDeferredValue: function(e) {
  var t = He();
  return oe === null ? t.memoizedState = e : uc(t, oe.memoizedState, e);
}, useTransition: function() {
  var e = hi(kr)[0], t = He().memoizedState;
  return [e, t];
}, useMutableSource: Ga, useSyncExternalStore: Ya, useId: sc, unstable_isNewReconciler: !1 };
function Ge(e, t) {
  if (e && e.defaultProps) {
    t = q({}, t), e = e.defaultProps;
    for (var n in e) t[n] === void 0 && (t[n] = e[n]);
    return t;
  }
  return t;
}
function bi(e, t, n, r) {
  t = e.memoizedState, n = n(r, t), n = n == null ? t : q({}, t, n), e.memoizedState = n, e.lanes === 0 && (e.updateQueue.baseState = n);
}
var Il = { isMounted: function(e) {
  return (e = e._reactInternals) ? tn(e) === e : !1;
}, enqueueSetState: function(e, t, n) {
  e = e._reactInternals;
  var r = Se(), l = Lt(e), i = dt(r, l);
  i.payload = t, n != null && (i.callback = n), t = Tt(e, i, l), t !== null && (Je(t, e, l, r), qr(t, e, l));
}, enqueueReplaceState: function(e, t, n) {
  e = e._reactInternals;
  var r = Se(), l = Lt(e), i = dt(r, l);
  i.tag = 1, i.payload = t, n != null && (i.callback = n), t = Tt(e, i, l), t !== null && (Je(t, e, l, r), qr(t, e, l));
}, enqueueForceUpdate: function(e, t) {
  e = e._reactInternals;
  var n = Se(), r = Lt(e), l = dt(n, r);
  l.tag = 2, t != null && (l.callback = t), t = Tt(e, l, r), t !== null && (Je(t, e, r, n), qr(t, e, r));
} };
function os(e, t, n, r, l, i, o) {
  return e = e.stateNode, typeof e.shouldComponentUpdate == "function" ? e.shouldComponentUpdate(r, i, o) : t.prototype && t.prototype.isPureReactComponent ? !pr(n, r) || !pr(l, i) : !0;
}
function fc(e, t, n) {
  var r = !1, l = Wt, i = t.contextType;
  return typeof i == "object" && i !== null ? i = Ke(i) : (l = je(t) ? Xt : we.current, r = t.contextTypes, i = (r = r != null) ? En(e, l) : Wt), t = new t(n, i), e.memoizedState = t.state !== null && t.state !== void 0 ? t.state : null, t.updater = Il, e.stateNode = t, t._reactInternals = e, r && (e = e.stateNode, e.__reactInternalMemoizedUnmaskedChildContext = l, e.__reactInternalMemoizedMaskedChildContext = i), t;
}
function us(e, t, n, r) {
  e = t.state, typeof t.componentWillReceiveProps == "function" && t.componentWillReceiveProps(n, r), typeof t.UNSAFE_componentWillReceiveProps == "function" && t.UNSAFE_componentWillReceiveProps(n, r), t.state !== e && Il.enqueueReplaceState(t, t.state, null);
}
function eo(e, t, n, r) {
  var l = e.stateNode;
  l.props = n, l.state = e.memoizedState, l.refs = {}, Ko(e);
  var i = t.contextType;
  typeof i == "object" && i !== null ? l.context = Ke(i) : (i = je(t) ? Xt : we.current, l.context = En(e, i)), l.state = e.memoizedState, i = t.getDerivedStateFromProps, typeof i == "function" && (bi(e, t, i, n), l.state = e.memoizedState), typeof t.getDerivedStateFromProps == "function" || typeof l.getSnapshotBeforeUpdate == "function" || typeof l.UNSAFE_componentWillMount != "function" && typeof l.componentWillMount != "function" || (t = l.state, typeof l.componentWillMount == "function" && l.componentWillMount(), typeof l.UNSAFE_componentWillMount == "function" && l.UNSAFE_componentWillMount(), t !== l.state && Il.enqueueReplaceState(l, l.state, null), Sl(e, n, l, r), l.state = e.memoizedState), typeof l.componentDidMount == "function" && (e.flags |= 4194308);
}
function Tn(e, t) {
  try {
    var n = "", r = t;
    do
      n += Cd(r), r = r.return;
    while (r);
    var l = n;
  } catch (i) {
    l = `
Error generating stack: ` + i.message + `
` + i.stack;
  }
  return { value: e, source: t, stack: l, digest: null };
}
function vi(e, t, n) {
  return { value: e, source: null, stack: n ?? null, digest: t ?? null };
}
function to(e, t) {
  try {
    console.error(t.value);
  } catch (n) {
    setTimeout(function() {
      throw n;
    });
  }
}
var tp = typeof WeakMap == "function" ? WeakMap : Map;
function pc(e, t, n) {
  n = dt(-1, n), n.tag = 3, n.payload = { element: null };
  var r = t.value;
  return n.callback = function() {
    Nl || (Nl = !0, fo = r), to(e, t);
  }, n;
}
function mc(e, t, n) {
  n = dt(-1, n), n.tag = 3;
  var r = e.type.getDerivedStateFromError;
  if (typeof r == "function") {
    var l = t.value;
    n.payload = function() {
      return r(l);
    }, n.callback = function() {
      to(e, t);
    };
  }
  var i = e.stateNode;
  return i !== null && typeof i.componentDidCatch == "function" && (n.callback = function() {
    to(e, t), typeof r != "function" && (Pt === null ? Pt = /* @__PURE__ */ new Set([this]) : Pt.add(this));
    var o = t.stack;
    this.componentDidCatch(t.value, { componentStack: o !== null ? o : "" });
  }), n;
}
function ss(e, t, n) {
  var r = e.pingCache;
  if (r === null) {
    r = e.pingCache = new tp();
    var l = /* @__PURE__ */ new Set();
    r.set(t, l);
  } else l = r.get(t), l === void 0 && (l = /* @__PURE__ */ new Set(), r.set(t, l));
  l.has(n) || (l.add(n), e = hp.bind(null, e, t, n), t.then(e, e));
}
function as(e) {
  do {
    var t;
    if ((t = e.tag === 13) && (t = e.memoizedState, t = t !== null ? t.dehydrated !== null : !0), t) return e;
    e = e.return;
  } while (e !== null);
  return null;
}
function cs(e, t, n, r, l) {
  return e.mode & 1 ? (e.flags |= 65536, e.lanes = l, e) : (e === t ? e.flags |= 65536 : (e.flags |= 128, n.flags |= 131072, n.flags &= -52805, n.tag === 1 && (n.alternate === null ? n.tag = 17 : (t = dt(-1, 1), t.tag = 2, Tt(n, t, 1))), n.lanes |= 1), e);
}
var np = vt.ReactCurrentOwner, Ne = !1;
function ke(e, t, n, r) {
  t.child = e === null ? Ba(t, null, n, r) : Cn(t, e.child, n, r);
}
function ds(e, t, n, r, l) {
  n = n.render;
  var i = t.ref;
  return _n(t, l), r = Xo(e, t, n, r, i, l), n = Zo(), e !== null && !Ne ? (t.updateQueue = e.updateQueue, t.flags &= -2053, e.lanes &= ~l, ht(e, t, l)) : (Y && n && Ao(t), t.flags |= 1, ke(e, t, r, l), t.child);
}
function fs(e, t, n, r, l) {
  if (e === null) {
    var i = n.type;
    return typeof i == "function" && !ou(i) && i.defaultProps === void 0 && n.compare === null && n.defaultProps === void 0 ? (t.tag = 15, t.type = i, hc(e, t, i, r, l)) : (e = ll(n.type, null, r, t, t.mode, l), e.ref = t.ref, e.return = t, t.child = e);
  }
  if (i = e.child, !(e.lanes & l)) {
    var o = i.memoizedProps;
    if (n = n.compare, n = n !== null ? n : pr, n(o, r) && e.ref === t.ref) return ht(e, t, l);
  }
  return t.flags |= 1, e = Mt(i, r), e.ref = t.ref, e.return = t, t.child = e;
}
function hc(e, t, n, r, l) {
  if (e !== null) {
    var i = e.memoizedProps;
    if (pr(i, r) && e.ref === t.ref) if (Ne = !1, t.pendingProps = r = i, (e.lanes & l) !== 0) e.flags & 131072 && (Ne = !0);
    else return t.lanes = e.lanes, ht(e, t, l);
  }
  return no(e, t, n, r, l);
}
function vc(e, t, n) {
  var r = t.pendingProps, l = r.children, i = e !== null ? e.memoizedState : null;
  if (r.mode === "hidden") if (!(t.mode & 1)) t.memoizedState = { baseLanes: 0, cachePool: null, transitions: null }, H(gn, Le), Le |= n;
  else {
    if (!(n & 1073741824)) return e = i !== null ? i.baseLanes | n : n, t.lanes = t.childLanes = 1073741824, t.memoizedState = { baseLanes: e, cachePool: null, transitions: null }, t.updateQueue = null, H(gn, Le), Le |= e, null;
    t.memoizedState = { baseLanes: 0, cachePool: null, transitions: null }, r = i !== null ? i.baseLanes : n, H(gn, Le), Le |= r;
  }
  else i !== null ? (r = i.baseLanes | n, t.memoizedState = null) : r = n, H(gn, Le), Le |= r;
  return ke(e, t, l, n), t.child;
}
function gc(e, t) {
  var n = t.ref;
  (e === null && n !== null || e !== null && e.ref !== n) && (t.flags |= 512, t.flags |= 2097152);
}
function no(e, t, n, r, l) {
  var i = je(n) ? Xt : we.current;
  return i = En(t, i), _n(t, l), n = Xo(e, t, n, r, i, l), r = Zo(), e !== null && !Ne ? (t.updateQueue = e.updateQueue, t.flags &= -2053, e.lanes &= ~l, ht(e, t, l)) : (Y && r && Ao(t), t.flags |= 1, ke(e, t, n, l), t.child);
}
function ps(e, t, n, r, l) {
  if (je(n)) {
    var i = !0;
    vl(t);
  } else i = !1;
  if (_n(t, l), t.stateNode === null) tl(e, t), fc(t, n, r), eo(t, n, r, l), r = !0;
  else if (e === null) {
    var o = t.stateNode, u = t.memoizedProps;
    o.props = u;
    var s = o.context, d = n.contextType;
    typeof d == "object" && d !== null ? d = Ke(d) : (d = je(n) ? Xt : we.current, d = En(t, d));
    var m = n.getDerivedStateFromProps, v = typeof m == "function" || typeof o.getSnapshotBeforeUpdate == "function";
    v || typeof o.UNSAFE_componentWillReceiveProps != "function" && typeof o.componentWillReceiveProps != "function" || (u !== r || s !== d) && us(t, o, r, d), kt = !1;
    var h = t.memoizedState;
    o.state = h, Sl(t, r, o, l), s = t.memoizedState, u !== r || h !== s || Ce.current || kt ? (typeof m == "function" && (bi(t, n, m, r), s = t.memoizedState), (u = kt || os(t, n, u, r, h, s, d)) ? (v || typeof o.UNSAFE_componentWillMount != "function" && typeof o.componentWillMount != "function" || (typeof o.componentWillMount == "function" && o.componentWillMount(), typeof o.UNSAFE_componentWillMount == "function" && o.UNSAFE_componentWillMount()), typeof o.componentDidMount == "function" && (t.flags |= 4194308)) : (typeof o.componentDidMount == "function" && (t.flags |= 4194308), t.memoizedProps = r, t.memoizedState = s), o.props = r, o.state = s, o.context = d, r = u) : (typeof o.componentDidMount == "function" && (t.flags |= 4194308), r = !1);
  } else {
    o = t.stateNode, Ha(e, t), u = t.memoizedProps, d = t.type === t.elementType ? u : Ge(t.type, u), o.props = d, v = t.pendingProps, h = o.context, s = n.contextType, typeof s == "object" && s !== null ? s = Ke(s) : (s = je(n) ? Xt : we.current, s = En(t, s));
    var _ = n.getDerivedStateFromProps;
    (m = typeof _ == "function" || typeof o.getSnapshotBeforeUpdate == "function") || typeof o.UNSAFE_componentWillReceiveProps != "function" && typeof o.componentWillReceiveProps != "function" || (u !== v || h !== s) && us(t, o, r, s), kt = !1, h = t.memoizedState, o.state = h, Sl(t, r, o, l);
    var x = t.memoizedState;
    u !== v || h !== x || Ce.current || kt ? (typeof _ == "function" && (bi(t, n, _, r), x = t.memoizedState), (d = kt || os(t, n, d, r, h, x, s) || !1) ? (m || typeof o.UNSAFE_componentWillUpdate != "function" && typeof o.componentWillUpdate != "function" || (typeof o.componentWillUpdate == "function" && o.componentWillUpdate(r, x, s), typeof o.UNSAFE_componentWillUpdate == "function" && o.UNSAFE_componentWillUpdate(r, x, s)), typeof o.componentDidUpdate == "function" && (t.flags |= 4), typeof o.getSnapshotBeforeUpdate == "function" && (t.flags |= 1024)) : (typeof o.componentDidUpdate != "function" || u === e.memoizedProps && h === e.memoizedState || (t.flags |= 4), typeof o.getSnapshotBeforeUpdate != "function" || u === e.memoizedProps && h === e.memoizedState || (t.flags |= 1024), t.memoizedProps = r, t.memoizedState = x), o.props = r, o.state = x, o.context = s, r = d) : (typeof o.componentDidUpdate != "function" || u === e.memoizedProps && h === e.memoizedState || (t.flags |= 4), typeof o.getSnapshotBeforeUpdate != "function" || u === e.memoizedProps && h === e.memoizedState || (t.flags |= 1024), r = !1);
  }
  return ro(e, t, n, r, i, l);
}
function ro(e, t, n, r, l, i) {
  gc(e, t);
  var o = (t.flags & 128) !== 0;
  if (!r && !o) return l && qu(t, n, !1), ht(e, t, i);
  r = t.stateNode, np.current = t;
  var u = o && typeof n.getDerivedStateFromError != "function" ? null : r.render();
  return t.flags |= 1, e !== null && o ? (t.child = Cn(t, e.child, null, i), t.child = Cn(t, null, u, i)) : ke(e, t, u, i), t.memoizedState = r.state, l && qu(t, n, !0), t.child;
}
function yc(e) {
  var t = e.stateNode;
  t.pendingContext ? Ju(e, t.pendingContext, t.pendingContext !== t.context) : t.context && Ju(e, t.context, !1), Ho(e, t.containerInfo);
}
function ms(e, t, n, r, l) {
  return Nn(), Do(l), t.flags |= 256, ke(e, t, n, r), t.child;
}
var lo = { dehydrated: null, treeContext: null, retryLane: 0 };
function io(e) {
  return { baseLanes: e, cachePool: null, transitions: null };
}
function wc(e, t, n) {
  var r = t.pendingProps, l = Z.current, i = !1, o = (t.flags & 128) !== 0, u;
  if ((u = o) || (u = e !== null && e.memoizedState === null ? !1 : (l & 2) !== 0), u ? (i = !0, t.flags &= -129) : (e === null || e.memoizedState !== null) && (l |= 1), H(Z, l & 1), e === null)
    return Ji(t), e = t.memoizedState, e !== null && (e = e.dehydrated, e !== null) ? (t.mode & 1 ? e.data === "$!" ? t.lanes = 8 : t.lanes = 1073741824 : t.lanes = 1, null) : (o = r.children, e = r.fallback, i ? (r = t.mode, i = t.child, o = { mode: "hidden", children: o }, !(r & 1) && i !== null ? (i.childLanes = 0, i.pendingProps = o) : i = $l(o, r, 0, null), e = Yt(e, r, n, null), i.return = t, e.return = t, i.sibling = e, t.child = i, t.child.memoizedState = io(n), t.memoizedState = lo, e) : bo(t, o));
  if (l = e.memoizedState, l !== null && (u = l.dehydrated, u !== null)) return rp(e, t, o, r, u, l, n);
  if (i) {
    i = r.fallback, o = t.mode, l = e.child, u = l.sibling;
    var s = { mode: "hidden", children: r.children };
    return !(o & 1) && t.child !== l ? (r = t.child, r.childLanes = 0, r.pendingProps = s, t.deletions = null) : (r = Mt(l, s), r.subtreeFlags = l.subtreeFlags & 14680064), u !== null ? i = Mt(u, i) : (i = Yt(i, o, n, null), i.flags |= 2), i.return = t, r.return = t, r.sibling = i, t.child = r, r = i, i = t.child, o = e.child.memoizedState, o = o === null ? io(n) : { baseLanes: o.baseLanes | n, cachePool: null, transitions: o.transitions }, i.memoizedState = o, i.childLanes = e.childLanes & ~n, t.memoizedState = lo, r;
  }
  return i = e.child, e = i.sibling, r = Mt(i, { mode: "visible", children: r.children }), !(t.mode & 1) && (r.lanes = n), r.return = t, r.sibling = null, e !== null && (n = t.deletions, n === null ? (t.deletions = [e], t.flags |= 16) : n.push(e)), t.child = r, t.memoizedState = null, r;
}
function bo(e, t) {
  return t = $l({ mode: "visible", children: t }, e.mode, 0, null), t.return = e, e.child = t;
}
function Vr(e, t, n, r) {
  return r !== null && Do(r), Cn(t, e.child, null, n), e = bo(t, t.pendingProps.children), e.flags |= 2, t.memoizedState = null, e;
}
function rp(e, t, n, r, l, i, o) {
  if (n)
    return t.flags & 256 ? (t.flags &= -257, r = vi(Error(w(422))), Vr(e, t, o, r)) : t.memoizedState !== null ? (t.child = e.child, t.flags |= 128, null) : (i = r.fallback, l = t.mode, r = $l({ mode: "visible", children: r.children }, l, 0, null), i = Yt(i, l, o, null), i.flags |= 2, r.return = t, i.return = t, r.sibling = i, t.child = r, t.mode & 1 && Cn(t, e.child, null, o), t.child.memoizedState = io(o), t.memoizedState = lo, i);
  if (!(t.mode & 1)) return Vr(e, t, o, null);
  if (l.data === "$!") {
    if (r = l.nextSibling && l.nextSibling.dataset, r) var u = r.dgst;
    return r = u, i = Error(w(419)), r = vi(i, r, void 0), Vr(e, t, o, r);
  }
  if (u = (o & e.childLanes) !== 0, Ne || u) {
    if (r = fe, r !== null) {
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
    return iu(), r = vi(Error(w(421))), Vr(e, t, o, r);
  }
  return l.data === "$?" ? (t.flags |= 128, t.child = e.child, t = vp.bind(null, e), l._reactRetry = t, null) : (e = i.treeContext, Me = jt(l.nextSibling), We = t, Y = !0, Xe = null, e !== null && (Ue[$e++] = at, Ue[$e++] = ct, Ue[$e++] = Zt, at = e.id, ct = e.overflow, Zt = t), t = bo(t, r.children), t.flags |= 4096, t);
}
function hs(e, t, n) {
  e.lanes |= t;
  var r = e.alternate;
  r !== null && (r.lanes |= t), qi(e.return, t, n);
}
function gi(e, t, n, r, l) {
  var i = e.memoizedState;
  i === null ? e.memoizedState = { isBackwards: t, rendering: null, renderingStartTime: 0, last: r, tail: n, tailMode: l } : (i.isBackwards = t, i.rendering = null, i.renderingStartTime = 0, i.last = r, i.tail = n, i.tailMode = l);
}
function kc(e, t, n) {
  var r = t.pendingProps, l = r.revealOrder, i = r.tail;
  if (ke(e, t, r.children, n), r = Z.current, r & 2) r = r & 1 | 2, t.flags |= 128;
  else {
    if (e !== null && e.flags & 128) e: for (e = t.child; e !== null; ) {
      if (e.tag === 13) e.memoizedState !== null && hs(e, n, t);
      else if (e.tag === 19) hs(e, n, t);
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
  if (H(Z, r), !(t.mode & 1)) t.memoizedState = null;
  else switch (l) {
    case "forwards":
      for (n = t.child, l = null; n !== null; ) e = n.alternate, e !== null && _l(e) === null && (l = n), n = n.sibling;
      n = l, n === null ? (l = t.child, t.child = null) : (l = n.sibling, n.sibling = null), gi(t, !1, l, n, i);
      break;
    case "backwards":
      for (n = null, l = t.child, t.child = null; l !== null; ) {
        if (e = l.alternate, e !== null && _l(e) === null) {
          t.child = l;
          break;
        }
        e = l.sibling, l.sibling = n, n = l, l = e;
      }
      gi(t, !0, n, null, i);
      break;
    case "together":
      gi(t, !1, null, null, void 0);
      break;
    default:
      t.memoizedState = null;
  }
  return t.child;
}
function tl(e, t) {
  !(t.mode & 1) && e !== null && (e.alternate = null, t.alternate = null, t.flags |= 2);
}
function ht(e, t, n) {
  if (e !== null && (t.dependencies = e.dependencies), qt |= t.lanes, !(n & t.childLanes)) return null;
  if (e !== null && t.child !== e.child) throw Error(w(153));
  if (t.child !== null) {
    for (e = t.child, n = Mt(e, e.pendingProps), t.child = n, n.return = t; e.sibling !== null; ) e = e.sibling, n = n.sibling = Mt(e, e.pendingProps), n.return = t;
    n.sibling = null;
  }
  return t.child;
}
function lp(e, t, n) {
  switch (t.tag) {
    case 3:
      yc(t), Nn();
      break;
    case 5:
      Qa(t);
      break;
    case 1:
      je(t.type) && vl(t);
      break;
    case 4:
      Ho(t, t.stateNode.containerInfo);
      break;
    case 10:
      var r = t.type._context, l = t.memoizedProps.value;
      H(wl, r._currentValue), r._currentValue = l;
      break;
    case 13:
      if (r = t.memoizedState, r !== null)
        return r.dehydrated !== null ? (H(Z, Z.current & 1), t.flags |= 128, null) : n & t.child.childLanes ? wc(e, t, n) : (H(Z, Z.current & 1), e = ht(e, t, n), e !== null ? e.sibling : null);
      H(Z, Z.current & 1);
      break;
    case 19:
      if (r = (n & t.childLanes) !== 0, e.flags & 128) {
        if (r) return kc(e, t, n);
        t.flags |= 128;
      }
      if (l = t.memoizedState, l !== null && (l.rendering = null, l.tail = null, l.lastEffect = null), H(Z, Z.current), r) break;
      return null;
    case 22:
    case 23:
      return t.lanes = 0, vc(e, t, n);
  }
  return ht(e, t, n);
}
var Sc, oo, _c, xc;
Sc = function(e, t) {
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
oo = function() {
};
_c = function(e, t, n, r) {
  var l = e.memoizedProps;
  if (l !== r) {
    e = t.stateNode, Qt(rt.current);
    var i = null;
    switch (n) {
      case "input":
        l = ji(e, l), r = ji(e, r), i = [];
        break;
      case "select":
        l = q({}, l, { value: void 0 }), r = q({}, r, { value: void 0 }), i = [];
        break;
      case "textarea":
        l = Li(e, l), r = Li(e, r), i = [];
        break;
      default:
        typeof l.onClick != "function" && typeof r.onClick == "function" && (e.onclick = ml);
    }
    Fi(n, r);
    var o;
    n = null;
    for (d in l) if (!r.hasOwnProperty(d) && l.hasOwnProperty(d) && l[d] != null) if (d === "style") {
      var u = l[d];
      for (o in u) u.hasOwnProperty(o) && (n || (n = {}), n[o] = "");
    } else d !== "dangerouslySetInnerHTML" && d !== "children" && d !== "suppressContentEditableWarning" && d !== "suppressHydrationWarning" && d !== "autoFocus" && (or.hasOwnProperty(d) ? i || (i = []) : (i = i || []).push(d, null));
    for (d in r) {
      var s = r[d];
      if (u = l != null ? l[d] : void 0, r.hasOwnProperty(d) && s !== u && (s != null || u != null)) if (d === "style") if (u) {
        for (o in u) !u.hasOwnProperty(o) || s && s.hasOwnProperty(o) || (n || (n = {}), n[o] = "");
        for (o in s) s.hasOwnProperty(o) && u[o] !== s[o] && (n || (n = {}), n[o] = s[o]);
      } else n || (i || (i = []), i.push(
        d,
        n
      )), n = s;
      else d === "dangerouslySetInnerHTML" ? (s = s ? s.__html : void 0, u = u ? u.__html : void 0, s != null && u !== s && (i = i || []).push(d, s)) : d === "children" ? typeof s != "string" && typeof s != "number" || (i = i || []).push(d, "" + s) : d !== "suppressContentEditableWarning" && d !== "suppressHydrationWarning" && (or.hasOwnProperty(d) ? (s != null && d === "onScroll" && Q("scroll", e), i || u === s || (i = [])) : (i = i || []).push(d, s));
    }
    n && (i = i || []).push("style", n);
    var d = i;
    (t.updateQueue = d) && (t.flags |= 4);
  }
};
xc = function(e, t, n, r) {
  n !== r && (t.flags |= 4);
};
function Bn(e, t) {
  if (!Y) switch (e.tailMode) {
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
function ge(e) {
  var t = e.alternate !== null && e.alternate.child === e.child, n = 0, r = 0;
  if (t) for (var l = e.child; l !== null; ) n |= l.lanes | l.childLanes, r |= l.subtreeFlags & 14680064, r |= l.flags & 14680064, l.return = e, l = l.sibling;
  else for (l = e.child; l !== null; ) n |= l.lanes | l.childLanes, r |= l.subtreeFlags, r |= l.flags, l.return = e, l = l.sibling;
  return e.subtreeFlags |= r, e.childLanes = n, t;
}
function ip(e, t, n) {
  var r = t.pendingProps;
  switch (Io(t), t.tag) {
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
      return ge(t), null;
    case 1:
      return je(t.type) && hl(), ge(t), null;
    case 3:
      return r = t.stateNode, jn(), G(Ce), G(we), Go(), r.pendingContext && (r.context = r.pendingContext, r.pendingContext = null), (e === null || e.child === null) && (Ur(t) ? t.flags |= 4 : e === null || e.memoizedState.isDehydrated && !(t.flags & 256) || (t.flags |= 1024, Xe !== null && (ho(Xe), Xe = null))), oo(e, t), ge(t), null;
    case 5:
      Qo(t);
      var l = Qt(yr.current);
      if (n = t.type, e !== null && t.stateNode != null) _c(e, t, n, r, l), e.ref !== t.ref && (t.flags |= 512, t.flags |= 2097152);
      else {
        if (!r) {
          if (t.stateNode === null) throw Error(w(166));
          return ge(t), null;
        }
        if (e = Qt(rt.current), Ur(t)) {
          r = t.stateNode, n = t.type;
          var i = t.memoizedProps;
          switch (r[tt] = t, r[vr] = i, e = (t.mode & 1) !== 0, n) {
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
              for (l = 0; l < Xn.length; l++) Q(Xn[l], r);
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
              zu(r, i), Q("invalid", r);
              break;
            case "select":
              r._wrapperState = { wasMultiple: !!i.multiple }, Q("invalid", r);
              break;
            case "textarea":
              Nu(r, i), Q("invalid", r);
          }
          Fi(n, i), l = null;
          for (var o in i) if (i.hasOwnProperty(o)) {
            var u = i[o];
            o === "children" ? typeof u == "string" ? r.textContent !== u && (i.suppressHydrationWarning !== !0 && Dr(r.textContent, u, e), l = ["children", u]) : typeof u == "number" && r.textContent !== "" + u && (i.suppressHydrationWarning !== !0 && Dr(
              r.textContent,
              u,
              e
            ), l = ["children", "" + u]) : or.hasOwnProperty(o) && u != null && o === "onScroll" && Q("scroll", r);
          }
          switch (n) {
            case "input":
              Lr(r), Eu(r, i, !0);
              break;
            case "textarea":
              Lr(r), Cu(r);
              break;
            case "select":
            case "option":
              break;
            default:
              typeof i.onClick == "function" && (r.onclick = ml);
          }
          r = l, t.updateQueue = r, r !== null && (t.flags |= 4);
        } else {
          o = l.nodeType === 9 ? l : l.ownerDocument, e === "http://www.w3.org/1999/xhtml" && (e = Js(n)), e === "http://www.w3.org/1999/xhtml" ? n === "script" ? (e = o.createElement("div"), e.innerHTML = "<script><\/script>", e = e.removeChild(e.firstChild)) : typeof r.is == "string" ? e = o.createElement(n, { is: r.is }) : (e = o.createElement(n), n === "select" && (o = e, r.multiple ? o.multiple = !0 : r.size && (o.size = r.size))) : e = o.createElementNS(e, n), e[tt] = t, e[vr] = r, Sc(e, t, !1, !1), t.stateNode = e;
          e: {
            switch (o = Wi(n, r), n) {
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
                for (l = 0; l < Xn.length; l++) Q(Xn[l], e);
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
                zu(e, r), l = ji(e, r), Q("invalid", e);
                break;
              case "option":
                l = r;
                break;
              case "select":
                e._wrapperState = { wasMultiple: !!r.multiple }, l = q({}, r, { value: void 0 }), Q("invalid", e);
                break;
              case "textarea":
                Nu(e, r), l = Li(e, r), Q("invalid", e);
                break;
              default:
                l = r;
            }
            Fi(n, l), u = l;
            for (i in u) if (u.hasOwnProperty(i)) {
              var s = u[i];
              i === "style" ? ea(e, s) : i === "dangerouslySetInnerHTML" ? (s = s ? s.__html : void 0, s != null && qs(e, s)) : i === "children" ? typeof s == "string" ? (n !== "textarea" || s !== "") && ur(e, s) : typeof s == "number" && ur(e, "" + s) : i !== "suppressContentEditableWarning" && i !== "suppressHydrationWarning" && i !== "autoFocus" && (or.hasOwnProperty(i) ? s != null && i === "onScroll" && Q("scroll", e) : s != null && xo(e, i, s, o));
            }
            switch (n) {
              case "input":
                Lr(e), Eu(e, r, !1);
                break;
              case "textarea":
                Lr(e), Cu(e);
                break;
              case "option":
                r.value != null && e.setAttribute("value", "" + Ft(r.value));
                break;
              case "select":
                e.multiple = !!r.multiple, i = r.value, i != null ? yn(e, !!r.multiple, i, !1) : r.defaultValue != null && yn(
                  e,
                  !!r.multiple,
                  r.defaultValue,
                  !0
                );
                break;
              default:
                typeof l.onClick == "function" && (e.onclick = ml);
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
      return ge(t), null;
    case 6:
      if (e && t.stateNode != null) xc(e, t, e.memoizedProps, r);
      else {
        if (typeof r != "string" && t.stateNode === null) throw Error(w(166));
        if (n = Qt(yr.current), Qt(rt.current), Ur(t)) {
          if (r = t.stateNode, n = t.memoizedProps, r[tt] = t, (i = r.nodeValue !== n) && (e = We, e !== null)) switch (e.tag) {
            case 3:
              Dr(r.nodeValue, n, (e.mode & 1) !== 0);
              break;
            case 5:
              e.memoizedProps.suppressHydrationWarning !== !0 && Dr(r.nodeValue, n, (e.mode & 1) !== 0);
          }
          i && (t.flags |= 4);
        } else r = (n.nodeType === 9 ? n : n.ownerDocument).createTextNode(r), r[tt] = t, t.stateNode = r;
      }
      return ge(t), null;
    case 13:
      if (G(Z), r = t.memoizedState, e === null || e.memoizedState !== null && e.memoizedState.dehydrated !== null) {
        if (Y && Me !== null && t.mode & 1 && !(t.flags & 128)) $a(), Nn(), t.flags |= 98560, i = !1;
        else if (i = Ur(t), r !== null && r.dehydrated !== null) {
          if (e === null) {
            if (!i) throw Error(w(318));
            if (i = t.memoizedState, i = i !== null ? i.dehydrated : null, !i) throw Error(w(317));
            i[tt] = t;
          } else Nn(), !(t.flags & 128) && (t.memoizedState = null), t.flags |= 4;
          ge(t), i = !1;
        } else Xe !== null && (ho(Xe), Xe = null), i = !0;
        if (!i) return t.flags & 65536 ? t : null;
      }
      return t.flags & 128 ? (t.lanes = n, t) : (r = r !== null, r !== (e !== null && e.memoizedState !== null) && r && (t.child.flags |= 8192, t.mode & 1 && (e === null || Z.current & 1 ? ue === 0 && (ue = 3) : iu())), t.updateQueue !== null && (t.flags |= 4), ge(t), null);
    case 4:
      return jn(), oo(e, t), e === null && mr(t.stateNode.containerInfo), ge(t), null;
    case 10:
      return Vo(t.type._context), ge(t), null;
    case 17:
      return je(t.type) && hl(), ge(t), null;
    case 19:
      if (G(Z), i = t.memoizedState, i === null) return ge(t), null;
      if (r = (t.flags & 128) !== 0, o = i.rendering, o === null) if (r) Bn(i, !1);
      else {
        if (ue !== 0 || e !== null && e.flags & 128) for (e = t.child; e !== null; ) {
          if (o = _l(e), o !== null) {
            for (t.flags |= 128, Bn(i, !1), r = o.updateQueue, r !== null && (t.updateQueue = r, t.flags |= 4), t.subtreeFlags = 0, r = n, n = t.child; n !== null; ) i = n, e = r, i.flags &= 14680066, o = i.alternate, o === null ? (i.childLanes = 0, i.lanes = e, i.child = null, i.subtreeFlags = 0, i.memoizedProps = null, i.memoizedState = null, i.updateQueue = null, i.dependencies = null, i.stateNode = null) : (i.childLanes = o.childLanes, i.lanes = o.lanes, i.child = o.child, i.subtreeFlags = 0, i.deletions = null, i.memoizedProps = o.memoizedProps, i.memoizedState = o.memoizedState, i.updateQueue = o.updateQueue, i.type = o.type, e = o.dependencies, i.dependencies = e === null ? null : { lanes: e.lanes, firstContext: e.firstContext }), n = n.sibling;
            return H(Z, Z.current & 1 | 2), t.child;
          }
          e = e.sibling;
        }
        i.tail !== null && re() > Pn && (t.flags |= 128, r = !0, Bn(i, !1), t.lanes = 4194304);
      }
      else {
        if (!r) if (e = _l(o), e !== null) {
          if (t.flags |= 128, r = !0, n = e.updateQueue, n !== null && (t.updateQueue = n, t.flags |= 4), Bn(i, !0), i.tail === null && i.tailMode === "hidden" && !o.alternate && !Y) return ge(t), null;
        } else 2 * re() - i.renderingStartTime > Pn && n !== 1073741824 && (t.flags |= 128, r = !0, Bn(i, !1), t.lanes = 4194304);
        i.isBackwards ? (o.sibling = t.child, t.child = o) : (n = i.last, n !== null ? n.sibling = o : t.child = o, i.last = o);
      }
      return i.tail !== null ? (t = i.tail, i.rendering = t, i.tail = t.sibling, i.renderingStartTime = re(), t.sibling = null, n = Z.current, H(Z, r ? n & 1 | 2 : n & 1), t) : (ge(t), null);
    case 22:
    case 23:
      return lu(), r = t.memoizedState !== null, e !== null && e.memoizedState !== null !== r && (t.flags |= 8192), r && t.mode & 1 ? Le & 1073741824 && (ge(t), t.subtreeFlags & 6 && (t.flags |= 8192)) : ge(t), null;
    case 24:
      return null;
    case 25:
      return null;
  }
  throw Error(w(156, t.tag));
}
function op(e, t) {
  switch (Io(t), t.tag) {
    case 1:
      return je(t.type) && hl(), e = t.flags, e & 65536 ? (t.flags = e & -65537 | 128, t) : null;
    case 3:
      return jn(), G(Ce), G(we), Go(), e = t.flags, e & 65536 && !(e & 128) ? (t.flags = e & -65537 | 128, t) : null;
    case 5:
      return Qo(t), null;
    case 13:
      if (G(Z), e = t.memoizedState, e !== null && e.dehydrated !== null) {
        if (t.alternate === null) throw Error(w(340));
        Nn();
      }
      return e = t.flags, e & 65536 ? (t.flags = e & -65537 | 128, t) : null;
    case 19:
      return G(Z), null;
    case 4:
      return jn(), null;
    case 10:
      return Vo(t.type._context), null;
    case 22:
    case 23:
      return lu(), null;
    case 24:
      return null;
    default:
      return null;
  }
}
var Br = !1, ye = !1, up = typeof WeakSet == "function" ? WeakSet : Set, j = null;
function vn(e, t) {
  var n = e.ref;
  if (n !== null) if (typeof n == "function") try {
    n(null);
  } catch (r) {
    ne(e, t, r);
  }
  else n.current = null;
}
function uo(e, t, n) {
  try {
    n();
  } catch (r) {
    ne(e, t, r);
  }
}
var vs = !1;
function sp(e, t) {
  if (Ki = dl, e = ja(), Ro(e)) {
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
        var o = 0, u = -1, s = -1, d = 0, m = 0, v = e, h = null;
        t: for (; ; ) {
          for (var _; v !== n || l !== 0 && v.nodeType !== 3 || (u = o + l), v !== i || r !== 0 && v.nodeType !== 3 || (s = o + r), v.nodeType === 3 && (o += v.nodeValue.length), (_ = v.firstChild) !== null; )
            h = v, v = _;
          for (; ; ) {
            if (v === e) break t;
            if (h === n && ++d === l && (u = o), h === i && ++m === r && (s = o), (_ = v.nextSibling) !== null) break;
            v = h, h = v.parentNode;
          }
          v = _;
        }
        n = u === -1 || s === -1 ? null : { start: u, end: s };
      } else n = null;
    }
    n = n || { start: 0, end: 0 };
  } else n = null;
  for (Hi = { focusedElem: e, selectionRange: n }, dl = !1, j = t; j !== null; ) if (t = j, e = t.child, (t.subtreeFlags & 1028) !== 0 && e !== null) e.return = t, j = e;
  else for (; j !== null; ) {
    t = j;
    try {
      var x = t.alternate;
      if (t.flags & 1024) switch (t.tag) {
        case 0:
        case 11:
        case 15:
          break;
        case 1:
          if (x !== null) {
            var k = x.memoizedProps, K = x.memoizedState, f = t.stateNode, c = f.getSnapshotBeforeUpdate(t.elementType === t.type ? k : Ge(t.type, k), K);
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
      ne(t, t.return, y);
    }
    if (e = t.sibling, e !== null) {
      e.return = t.return, j = e;
      break;
    }
    j = t.return;
  }
  return x = vs, vs = !1, x;
}
function rr(e, t, n) {
  var r = t.updateQueue;
  if (r = r !== null ? r.lastEffect : null, r !== null) {
    var l = r = r.next;
    do {
      if ((l.tag & e) === e) {
        var i = l.destroy;
        l.destroy = void 0, i !== void 0 && uo(t, n, i);
      }
      l = l.next;
    } while (l !== r);
  }
}
function Dl(e, t) {
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
function so(e) {
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
function zc(e) {
  var t = e.alternate;
  t !== null && (e.alternate = null, zc(t)), e.child = null, e.deletions = null, e.sibling = null, e.tag === 5 && (t = e.stateNode, t !== null && (delete t[tt], delete t[vr], delete t[Yi], delete t[Kf], delete t[Hf])), e.stateNode = null, e.return = null, e.dependencies = null, e.memoizedProps = null, e.memoizedState = null, e.pendingProps = null, e.stateNode = null, e.updateQueue = null;
}
function Ec(e) {
  return e.tag === 5 || e.tag === 3 || e.tag === 4;
}
function gs(e) {
  e: for (; ; ) {
    for (; e.sibling === null; ) {
      if (e.return === null || Ec(e.return)) return null;
      e = e.return;
    }
    for (e.sibling.return = e.return, e = e.sibling; e.tag !== 5 && e.tag !== 6 && e.tag !== 18; ) {
      if (e.flags & 2 || e.child === null || e.tag === 4) continue e;
      e.child.return = e, e = e.child;
    }
    if (!(e.flags & 2)) return e.stateNode;
  }
}
function ao(e, t, n) {
  var r = e.tag;
  if (r === 5 || r === 6) e = e.stateNode, t ? n.nodeType === 8 ? n.parentNode.insertBefore(e, t) : n.insertBefore(e, t) : (n.nodeType === 8 ? (t = n.parentNode, t.insertBefore(e, n)) : (t = n, t.appendChild(e)), n = n._reactRootContainer, n != null || t.onclick !== null || (t.onclick = ml));
  else if (r !== 4 && (e = e.child, e !== null)) for (ao(e, t, n), e = e.sibling; e !== null; ) ao(e, t, n), e = e.sibling;
}
function co(e, t, n) {
  var r = e.tag;
  if (r === 5 || r === 6) e = e.stateNode, t ? n.insertBefore(e, t) : n.appendChild(e);
  else if (r !== 4 && (e = e.child, e !== null)) for (co(e, t, n), e = e.sibling; e !== null; ) co(e, t, n), e = e.sibling;
}
var pe = null, Ye = !1;
function yt(e, t, n) {
  for (n = n.child; n !== null; ) Nc(e, t, n), n = n.sibling;
}
function Nc(e, t, n) {
  if (nt && typeof nt.onCommitFiberUnmount == "function") try {
    nt.onCommitFiberUnmount(Ll, n);
  } catch {
  }
  switch (n.tag) {
    case 5:
      ye || vn(n, t);
    case 6:
      var r = pe, l = Ye;
      pe = null, yt(e, t, n), pe = r, Ye = l, pe !== null && (Ye ? (e = pe, n = n.stateNode, e.nodeType === 8 ? e.parentNode.removeChild(n) : e.removeChild(n)) : pe.removeChild(n.stateNode));
      break;
    case 18:
      pe !== null && (Ye ? (e = pe, n = n.stateNode, e.nodeType === 8 ? ci(e.parentNode, n) : e.nodeType === 1 && ci(e, n), dr(e)) : ci(pe, n.stateNode));
      break;
    case 4:
      r = pe, l = Ye, pe = n.stateNode.containerInfo, Ye = !0, yt(e, t, n), pe = r, Ye = l;
      break;
    case 0:
    case 11:
    case 14:
    case 15:
      if (!ye && (r = n.updateQueue, r !== null && (r = r.lastEffect, r !== null))) {
        l = r = r.next;
        do {
          var i = l, o = i.destroy;
          i = i.tag, o !== void 0 && (i & 2 || i & 4) && uo(n, t, o), l = l.next;
        } while (l !== r);
      }
      yt(e, t, n);
      break;
    case 1:
      if (!ye && (vn(n, t), r = n.stateNode, typeof r.componentWillUnmount == "function")) try {
        r.props = n.memoizedProps, r.state = n.memoizedState, r.componentWillUnmount();
      } catch (u) {
        ne(n, t, u);
      }
      yt(e, t, n);
      break;
    case 21:
      yt(e, t, n);
      break;
    case 22:
      n.mode & 1 ? (ye = (r = ye) || n.memoizedState !== null, yt(e, t, n), ye = r) : yt(e, t, n);
      break;
    default:
      yt(e, t, n);
  }
}
function ys(e) {
  var t = e.updateQueue;
  if (t !== null) {
    e.updateQueue = null;
    var n = e.stateNode;
    n === null && (n = e.stateNode = new up()), t.forEach(function(r) {
      var l = gp.bind(null, e, r);
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
            pe = u.stateNode, Ye = !1;
            break e;
          case 3:
            pe = u.stateNode.containerInfo, Ye = !0;
            break e;
          case 4:
            pe = u.stateNode.containerInfo, Ye = !0;
            break e;
        }
        u = u.return;
      }
      if (pe === null) throw Error(w(160));
      Nc(i, o, l), pe = null, Ye = !1;
      var s = l.alternate;
      s !== null && (s.return = null), l.return = null;
    } catch (d) {
      ne(l, t, d);
    }
  }
  if (t.subtreeFlags & 12854) for (t = t.child; t !== null; ) Cc(t, e), t = t.sibling;
}
function Cc(e, t) {
  var n = e.alternate, r = e.flags;
  switch (e.tag) {
    case 0:
    case 11:
    case 14:
    case 15:
      if (Qe(t, e), be(e), r & 4) {
        try {
          rr(3, e, e.return), Dl(3, e);
        } catch (k) {
          ne(e, e.return, k);
        }
        try {
          rr(5, e, e.return);
        } catch (k) {
          ne(e, e.return, k);
        }
      }
      break;
    case 1:
      Qe(t, e), be(e), r & 512 && n !== null && vn(n, n.return);
      break;
    case 5:
      if (Qe(t, e), be(e), r & 512 && n !== null && vn(n, n.return), e.flags & 32) {
        var l = e.stateNode;
        try {
          ur(l, "");
        } catch (k) {
          ne(e, e.return, k);
        }
      }
      if (r & 4 && (l = e.stateNode, l != null)) {
        var i = e.memoizedProps, o = n !== null ? n.memoizedProps : i, u = e.type, s = e.updateQueue;
        if (e.updateQueue = null, s !== null) try {
          u === "input" && i.type === "radio" && i.name != null && Xs(l, i), Wi(u, o);
          var d = Wi(u, i);
          for (o = 0; o < s.length; o += 2) {
            var m = s[o], v = s[o + 1];
            m === "style" ? ea(l, v) : m === "dangerouslySetInnerHTML" ? qs(l, v) : m === "children" ? ur(l, v) : xo(l, m, v, d);
          }
          switch (u) {
            case "input":
              Ti(l, i);
              break;
            case "textarea":
              Zs(l, i);
              break;
            case "select":
              var h = l._wrapperState.wasMultiple;
              l._wrapperState.wasMultiple = !!i.multiple;
              var _ = i.value;
              _ != null ? yn(l, !!i.multiple, _, !1) : h !== !!i.multiple && (i.defaultValue != null ? yn(
                l,
                !!i.multiple,
                i.defaultValue,
                !0
              ) : yn(l, !!i.multiple, i.multiple ? [] : "", !1));
          }
          l[vr] = i;
        } catch (k) {
          ne(e, e.return, k);
        }
      }
      break;
    case 6:
      if (Qe(t, e), be(e), r & 4) {
        if (e.stateNode === null) throw Error(w(162));
        l = e.stateNode, i = e.memoizedProps;
        try {
          l.nodeValue = i;
        } catch (k) {
          ne(e, e.return, k);
        }
      }
      break;
    case 3:
      if (Qe(t, e), be(e), r & 4 && n !== null && n.memoizedState.isDehydrated) try {
        dr(t.containerInfo);
      } catch (k) {
        ne(e, e.return, k);
      }
      break;
    case 4:
      Qe(t, e), be(e);
      break;
    case 13:
      Qe(t, e), be(e), l = e.child, l.flags & 8192 && (i = l.memoizedState !== null, l.stateNode.isHidden = i, !i || l.alternate !== null && l.alternate.memoizedState !== null || (nu = re())), r & 4 && ys(e);
      break;
    case 22:
      if (m = n !== null && n.memoizedState !== null, e.mode & 1 ? (ye = (d = ye) || m, Qe(t, e), ye = d) : Qe(t, e), be(e), r & 8192) {
        if (d = e.memoizedState !== null, (e.stateNode.isHidden = d) && !m && e.mode & 1) for (j = e, m = e.child; m !== null; ) {
          for (v = j = m; j !== null; ) {
            switch (h = j, _ = h.child, h.tag) {
              case 0:
              case 11:
              case 14:
              case 15:
                rr(4, h, h.return);
                break;
              case 1:
                vn(h, h.return);
                var x = h.stateNode;
                if (typeof x.componentWillUnmount == "function") {
                  r = h, n = h.return;
                  try {
                    t = r, x.props = t.memoizedProps, x.state = t.memoizedState, x.componentWillUnmount();
                  } catch (k) {
                    ne(r, n, k);
                  }
                }
                break;
              case 5:
                vn(h, h.return);
                break;
              case 22:
                if (h.memoizedState !== null) {
                  ks(v);
                  continue;
                }
            }
            _ !== null ? (_.return = h, j = _) : ks(v);
          }
          m = m.sibling;
        }
        e: for (m = null, v = e; ; ) {
          if (v.tag === 5) {
            if (m === null) {
              m = v;
              try {
                l = v.stateNode, d ? (i = l.style, typeof i.setProperty == "function" ? i.setProperty("display", "none", "important") : i.display = "none") : (u = v.stateNode, s = v.memoizedProps.style, o = s != null && s.hasOwnProperty("display") ? s.display : null, u.style.display = bs("display", o));
              } catch (k) {
                ne(e, e.return, k);
              }
            }
          } else if (v.tag === 6) {
            if (m === null) try {
              v.stateNode.nodeValue = d ? "" : v.memoizedProps;
            } catch (k) {
              ne(e, e.return, k);
            }
          } else if ((v.tag !== 22 && v.tag !== 23 || v.memoizedState === null || v === e) && v.child !== null) {
            v.child.return = v, v = v.child;
            continue;
          }
          if (v === e) break e;
          for (; v.sibling === null; ) {
            if (v.return === null || v.return === e) break e;
            m === v && (m = null), v = v.return;
          }
          m === v && (m = null), v.sibling.return = v.return, v = v.sibling;
        }
      }
      break;
    case 19:
      Qe(t, e), be(e), r & 4 && ys(e);
      break;
    case 21:
      break;
    default:
      Qe(
        t,
        e
      ), be(e);
  }
}
function be(e) {
  var t = e.flags;
  if (t & 2) {
    try {
      e: {
        for (var n = e.return; n !== null; ) {
          if (Ec(n)) {
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
          r.flags & 32 && (ur(l, ""), r.flags &= -33);
          var i = gs(e);
          co(e, i, l);
          break;
        case 3:
        case 4:
          var o = r.stateNode.containerInfo, u = gs(e);
          ao(e, u, o);
          break;
        default:
          throw Error(w(161));
      }
    } catch (s) {
      ne(e, e.return, s);
    }
    e.flags &= -3;
  }
  t & 4096 && (e.flags &= -4097);
}
function ap(e, t, n) {
  j = e, jc(e);
}
function jc(e, t, n) {
  for (var r = (e.mode & 1) !== 0; j !== null; ) {
    var l = j, i = l.child;
    if (l.tag === 22 && r) {
      var o = l.memoizedState !== null || Br;
      if (!o) {
        var u = l.alternate, s = u !== null && u.memoizedState !== null || ye;
        u = Br;
        var d = ye;
        if (Br = o, (ye = s) && !d) for (j = l; j !== null; ) o = j, s = o.child, o.tag === 22 && o.memoizedState !== null ? Ss(l) : s !== null ? (s.return = o, j = s) : Ss(l);
        for (; i !== null; ) j = i, jc(i), i = i.sibling;
        j = l, Br = u, ye = d;
      }
      ws(e);
    } else l.subtreeFlags & 8772 && i !== null ? (i.return = l, j = i) : ws(e);
  }
}
function ws(e) {
  for (; j !== null; ) {
    var t = j;
    if (t.flags & 8772) {
      var n = t.alternate;
      try {
        if (t.flags & 8772) switch (t.tag) {
          case 0:
          case 11:
          case 15:
            ye || Dl(5, t);
            break;
          case 1:
            var r = t.stateNode;
            if (t.flags & 4 && !ye) if (n === null) r.componentDidMount();
            else {
              var l = t.elementType === t.type ? n.memoizedProps : Ge(t.type, n.memoizedProps);
              r.componentDidUpdate(l, n.memoizedState, r.__reactInternalSnapshotBeforeUpdate);
            }
            var i = t.updateQueue;
            i !== null && rs(t, i, r);
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
              rs(t, o, n);
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
                  var v = m.dehydrated;
                  v !== null && dr(v);
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
        ye || t.flags & 512 && so(t);
      } catch (h) {
        ne(t, t.return, h);
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
function ks(e) {
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
function Ss(e) {
  for (; j !== null; ) {
    var t = j;
    try {
      switch (t.tag) {
        case 0:
        case 11:
        case 15:
          var n = t.return;
          try {
            Dl(4, t);
          } catch (s) {
            ne(t, n, s);
          }
          break;
        case 1:
          var r = t.stateNode;
          if (typeof r.componentDidMount == "function") {
            var l = t.return;
            try {
              r.componentDidMount();
            } catch (s) {
              ne(t, l, s);
            }
          }
          var i = t.return;
          try {
            so(t);
          } catch (s) {
            ne(t, i, s);
          }
          break;
        case 5:
          var o = t.return;
          try {
            so(t);
          } catch (s) {
            ne(t, o, s);
          }
      }
    } catch (s) {
      ne(t, t.return, s);
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
var cp = Math.ceil, El = vt.ReactCurrentDispatcher, eu = vt.ReactCurrentOwner, Be = vt.ReactCurrentBatchConfig, U = 0, fe = null, ie = null, me = 0, Le = 0, gn = Rt(0), ue = 0, _r = null, qt = 0, Ul = 0, tu = 0, lr = null, Ee = null, nu = 0, Pn = 1 / 0, ut = null, Nl = !1, fo = null, Pt = null, Kr = !1, zt = null, Cl = 0, ir = 0, po = null, nl = -1, rl = 0;
function Se() {
  return U & 6 ? re() : nl !== -1 ? nl : nl = re();
}
function Lt(e) {
  return e.mode & 1 ? U & 2 && me !== 0 ? me & -me : Gf.transition !== null ? (rl === 0 && (rl = fa()), rl) : (e = $, e !== 0 || (e = window.event, e = e === void 0 ? 16 : wa(e.type)), e) : 1;
}
function Je(e, t, n, r) {
  if (50 < ir) throw ir = 0, po = null, Error(w(185));
  zr(e, n, r), (!(U & 2) || e !== fe) && (e === fe && (!(U & 2) && (Ul |= n), ue === 4 && _t(e, me)), Te(e, r), n === 1 && U === 0 && !(t.mode & 1) && (Pn = re() + 500, Rl && At()));
}
function Te(e, t) {
  var n = e.callbackNode;
  Qd(e, t);
  var r = cl(e, e === fe ? me : 0);
  if (r === 0) n !== null && Pu(n), e.callbackNode = null, e.callbackPriority = 0;
  else if (t = r & -r, e.callbackPriority !== t) {
    if (n != null && Pu(n), t === 1) e.tag === 0 ? Qf(_s.bind(null, e)) : Ia(_s.bind(null, e)), Vf(function() {
      !(U & 6) && At();
    }), n = null;
    else {
      switch (pa(r)) {
        case 1:
          n = jo;
          break;
        case 4:
          n = ca;
          break;
        case 16:
          n = al;
          break;
        case 536870912:
          n = da;
          break;
        default:
          n = al;
      }
      n = Rc(n, Tc.bind(null, e));
    }
    e.callbackPriority = t, e.callbackNode = n;
  }
}
function Tc(e, t) {
  if (nl = -1, rl = 0, U & 6) throw Error(w(327));
  var n = e.callbackNode;
  if (xn() && e.callbackNode !== n) return null;
  var r = cl(e, e === fe ? me : 0);
  if (r === 0) return null;
  if (r & 30 || r & e.expiredLanes || t) t = jl(e, r);
  else {
    t = r;
    var l = U;
    U |= 2;
    var i = Lc();
    (fe !== e || me !== t) && (ut = null, Pn = re() + 500, Gt(e, t));
    do
      try {
        pp();
        break;
      } catch (u) {
        Pc(e, u);
      }
    while (!0);
    $o(), El.current = i, U = l, ie !== null ? t = 0 : (fe = null, me = 0, t = ue);
  }
  if (t !== 0) {
    if (t === 2 && (l = Di(e), l !== 0 && (r = l, t = mo(e, l))), t === 1) throw n = _r, Gt(e, 0), _t(e, r), Te(e, re()), n;
    if (t === 6) _t(e, r);
    else {
      if (l = e.current.alternate, !(r & 30) && !dp(l) && (t = jl(e, r), t === 2 && (i = Di(e), i !== 0 && (r = i, t = mo(e, i))), t === 1)) throw n = _r, Gt(e, 0), _t(e, r), Te(e, re()), n;
      switch (e.finishedWork = l, e.finishedLanes = r, t) {
        case 0:
        case 1:
          throw Error(w(345));
        case 2:
          Bt(e, Ee, ut);
          break;
        case 3:
          if (_t(e, r), (r & 130023424) === r && (t = nu + 500 - re(), 10 < t)) {
            if (cl(e, 0) !== 0) break;
            if (l = e.suspendedLanes, (l & r) !== r) {
              Se(), e.pingedLanes |= e.suspendedLanes & l;
              break;
            }
            e.timeoutHandle = Gi(Bt.bind(null, e, Ee, ut), t);
            break;
          }
          Bt(e, Ee, ut);
          break;
        case 4:
          if (_t(e, r), (r & 4194240) === r) break;
          for (t = e.eventTimes, l = -1; 0 < r; ) {
            var o = 31 - Ze(r);
            i = 1 << o, o = t[o], o > l && (l = o), r &= ~i;
          }
          if (r = l, r = re() - r, r = (120 > r ? 120 : 480 > r ? 480 : 1080 > r ? 1080 : 1920 > r ? 1920 : 3e3 > r ? 3e3 : 4320 > r ? 4320 : 1960 * cp(r / 1960)) - r, 10 < r) {
            e.timeoutHandle = Gi(Bt.bind(null, e, Ee, ut), r);
            break;
          }
          Bt(e, Ee, ut);
          break;
        case 5:
          Bt(e, Ee, ut);
          break;
        default:
          throw Error(w(329));
      }
    }
  }
  return Te(e, re()), e.callbackNode === n ? Tc.bind(null, e) : null;
}
function mo(e, t) {
  var n = lr;
  return e.current.memoizedState.isDehydrated && (Gt(e, t).flags |= 256), e = jl(e, t), e !== 2 && (t = Ee, Ee = n, t !== null && ho(t)), e;
}
function ho(e) {
  Ee === null ? Ee = e : Ee.push.apply(Ee, e);
}
function dp(e) {
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
function _t(e, t) {
  for (t &= ~tu, t &= ~Ul, e.suspendedLanes |= t, e.pingedLanes &= ~t, e = e.expirationTimes; 0 < t; ) {
    var n = 31 - Ze(t), r = 1 << n;
    e[n] = -1, t &= ~r;
  }
}
function _s(e) {
  if (U & 6) throw Error(w(327));
  xn();
  var t = cl(e, 0);
  if (!(t & 1)) return Te(e, re()), null;
  var n = jl(e, t);
  if (e.tag !== 0 && n === 2) {
    var r = Di(e);
    r !== 0 && (t = r, n = mo(e, r));
  }
  if (n === 1) throw n = _r, Gt(e, 0), _t(e, t), Te(e, re()), n;
  if (n === 6) throw Error(w(345));
  return e.finishedWork = e.current.alternate, e.finishedLanes = t, Bt(e, Ee, ut), Te(e, re()), null;
}
function ru(e, t) {
  var n = U;
  U |= 1;
  try {
    return e(t);
  } finally {
    U = n, U === 0 && (Pn = re() + 500, Rl && At());
  }
}
function bt(e) {
  zt !== null && zt.tag === 0 && !(U & 6) && xn();
  var t = U;
  U |= 1;
  var n = Be.transition, r = $;
  try {
    if (Be.transition = null, $ = 1, e) return e();
  } finally {
    $ = r, Be.transition = n, U = t, !(U & 6) && At();
  }
}
function lu() {
  Le = gn.current, G(gn);
}
function Gt(e, t) {
  e.finishedWork = null, e.finishedLanes = 0;
  var n = e.timeoutHandle;
  if (n !== -1 && (e.timeoutHandle = -1, $f(n)), ie !== null) for (n = ie.return; n !== null; ) {
    var r = n;
    switch (Io(r), r.tag) {
      case 1:
        r = r.type.childContextTypes, r != null && hl();
        break;
      case 3:
        jn(), G(Ce), G(we), Go();
        break;
      case 5:
        Qo(r);
        break;
      case 4:
        jn();
        break;
      case 13:
        G(Z);
        break;
      case 19:
        G(Z);
        break;
      case 10:
        Vo(r.type._context);
        break;
      case 22:
      case 23:
        lu();
    }
    n = n.return;
  }
  if (fe = e, ie = e = Mt(e.current, null), me = Le = t, ue = 0, _r = null, tu = Ul = qt = 0, Ee = lr = null, Ht !== null) {
    for (t = 0; t < Ht.length; t++) if (n = Ht[t], r = n.interleaved, r !== null) {
      n.interleaved = null;
      var l = r.next, i = n.pending;
      if (i !== null) {
        var o = i.next;
        i.next = l, r.next = o;
      }
      n.pending = r;
    }
    Ht = null;
  }
  return e;
}
function Pc(e, t) {
  do {
    var n = ie;
    try {
      if ($o(), br.current = zl, xl) {
        for (var r = J.memoizedState; r !== null; ) {
          var l = r.queue;
          l !== null && (l.pending = null), r = r.next;
        }
        xl = !1;
      }
      if (Jt = 0, de = oe = J = null, nr = !1, wr = 0, eu.current = null, n === null || n.return === null) {
        ue = 1, _r = t, ie = null;
        break;
      }
      e: {
        var i = e, o = n.return, u = n, s = t;
        if (t = me, u.flags |= 32768, s !== null && typeof s == "object" && typeof s.then == "function") {
          var d = s, m = u, v = m.tag;
          if (!(m.mode & 1) && (v === 0 || v === 11 || v === 15)) {
            var h = m.alternate;
            h ? (m.updateQueue = h.updateQueue, m.memoizedState = h.memoizedState, m.lanes = h.lanes) : (m.updateQueue = null, m.memoizedState = null);
          }
          var _ = as(o);
          if (_ !== null) {
            _.flags &= -257, cs(_, o, u, i, t), _.mode & 1 && ss(i, d, t), t = _, s = d;
            var x = t.updateQueue;
            if (x === null) {
              var k = /* @__PURE__ */ new Set();
              k.add(s), t.updateQueue = k;
            } else x.add(s);
            break e;
          } else {
            if (!(t & 1)) {
              ss(i, d, t), iu();
              break e;
            }
            s = Error(w(426));
          }
        } else if (Y && u.mode & 1) {
          var K = as(o);
          if (K !== null) {
            !(K.flags & 65536) && (K.flags |= 256), cs(K, o, u, i, t), Do(Tn(s, u));
            break e;
          }
        }
        i = s = Tn(s, u), ue !== 4 && (ue = 2), lr === null ? lr = [i] : lr.push(i), i = o;
        do {
          switch (i.tag) {
            case 3:
              i.flags |= 65536, t &= -t, i.lanes |= t;
              var f = pc(i, s, t);
              ns(i, f);
              break e;
            case 1:
              u = s;
              var c = i.type, p = i.stateNode;
              if (!(i.flags & 128) && (typeof c.getDerivedStateFromError == "function" || p !== null && typeof p.componentDidCatch == "function" && (Pt === null || !Pt.has(p)))) {
                i.flags |= 65536, t &= -t, i.lanes |= t;
                var y = mc(i, u, t);
                ns(i, y);
                break e;
              }
          }
          i = i.return;
        } while (i !== null);
      }
      Fc(n);
    } catch (E) {
      t = E, ie === n && n !== null && (ie = n = n.return);
      continue;
    }
    break;
  } while (!0);
}
function Lc() {
  var e = El.current;
  return El.current = zl, e === null ? zl : e;
}
function iu() {
  (ue === 0 || ue === 3 || ue === 2) && (ue = 4), fe === null || !(qt & 268435455) && !(Ul & 268435455) || _t(fe, me);
}
function jl(e, t) {
  var n = U;
  U |= 2;
  var r = Lc();
  (fe !== e || me !== t) && (ut = null, Gt(e, t));
  do
    try {
      fp();
      break;
    } catch (l) {
      Pc(e, l);
    }
  while (!0);
  if ($o(), U = n, El.current = r, ie !== null) throw Error(w(261));
  return fe = null, me = 0, ue;
}
function fp() {
  for (; ie !== null; ) Mc(ie);
}
function pp() {
  for (; ie !== null && !Ad(); ) Mc(ie);
}
function Mc(e) {
  var t = Oc(e.alternate, e, Le);
  e.memoizedProps = e.pendingProps, t === null ? Fc(e) : ie = t, eu.current = null;
}
function Fc(e) {
  var t = e;
  do {
    var n = t.alternate;
    if (e = t.return, t.flags & 32768) {
      if (n = op(n, t), n !== null) {
        n.flags &= 32767, ie = n;
        return;
      }
      if (e !== null) e.flags |= 32768, e.subtreeFlags = 0, e.deletions = null;
      else {
        ue = 6, ie = null;
        return;
      }
    } else if (n = ip(n, t, Le), n !== null) {
      ie = n;
      return;
    }
    if (t = t.sibling, t !== null) {
      ie = t;
      return;
    }
    ie = t = e;
  } while (t !== null);
  ue === 0 && (ue = 5);
}
function Bt(e, t, n) {
  var r = $, l = Be.transition;
  try {
    Be.transition = null, $ = 1, mp(e, t, n, r);
  } finally {
    Be.transition = l, $ = r;
  }
  return null;
}
function mp(e, t, n, r) {
  do
    xn();
  while (zt !== null);
  if (U & 6) throw Error(w(327));
  n = e.finishedWork;
  var l = e.finishedLanes;
  if (n === null) return null;
  if (e.finishedWork = null, e.finishedLanes = 0, n === e.current) throw Error(w(177));
  e.callbackNode = null, e.callbackPriority = 0;
  var i = n.lanes | n.childLanes;
  if (Gd(e, i), e === fe && (ie = fe = null, me = 0), !(n.subtreeFlags & 2064) && !(n.flags & 2064) || Kr || (Kr = !0, Rc(al, function() {
    return xn(), null;
  })), i = (n.flags & 15990) !== 0, n.subtreeFlags & 15990 || i) {
    i = Be.transition, Be.transition = null;
    var o = $;
    $ = 1;
    var u = U;
    U |= 4, eu.current = null, sp(e, n), Cc(n, e), Wf(Hi), dl = !!Ki, Hi = Ki = null, e.current = n, ap(n), Id(), U = u, $ = o, Be.transition = i;
  } else e.current = n;
  if (Kr && (Kr = !1, zt = e, Cl = l), i = e.pendingLanes, i === 0 && (Pt = null), $d(n.stateNode), Te(e, re()), t !== null) for (r = e.onRecoverableError, n = 0; n < t.length; n++) l = t[n], r(l.value, { componentStack: l.stack, digest: l.digest });
  if (Nl) throw Nl = !1, e = fo, fo = null, e;
  return Cl & 1 && e.tag !== 0 && xn(), i = e.pendingLanes, i & 1 ? e === po ? ir++ : (ir = 0, po = e) : ir = 0, At(), null;
}
function xn() {
  if (zt !== null) {
    var e = pa(Cl), t = Be.transition, n = $;
    try {
      if (Be.transition = null, $ = 16 > e ? 16 : e, zt === null) var r = !1;
      else {
        if (e = zt, zt = null, Cl = 0, U & 6) throw Error(w(331));
        var l = U;
        for (U |= 4, j = e.current; j !== null; ) {
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
                      rr(8, m, i);
                  }
                  var v = m.child;
                  if (v !== null) v.return = m, j = v;
                  else for (; j !== null; ) {
                    m = j;
                    var h = m.sibling, _ = m.return;
                    if (zc(m), m === d) {
                      j = null;
                      break;
                    }
                    if (h !== null) {
                      h.return = _, j = h;
                      break;
                    }
                    j = _;
                  }
                }
              }
              var x = i.alternate;
              if (x !== null) {
                var k = x.child;
                if (k !== null) {
                  x.child = null;
                  do {
                    var K = k.sibling;
                    k.sibling = null, k = K;
                  } while (k !== null);
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
                rr(9, i, i.return);
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
                  Dl(9, u);
              }
            } catch (E) {
              ne(u, u.return, E);
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
        if (U = l, At(), nt && typeof nt.onPostCommitFiberRoot == "function") try {
          nt.onPostCommitFiberRoot(Ll, e);
        } catch {
        }
        r = !0;
      }
      return r;
    } finally {
      $ = n, Be.transition = t;
    }
  }
  return !1;
}
function xs(e, t, n) {
  t = Tn(n, t), t = pc(e, t, 1), e = Tt(e, t, 1), t = Se(), e !== null && (zr(e, 1, t), Te(e, t));
}
function ne(e, t, n) {
  if (e.tag === 3) xs(e, e, n);
  else for (; t !== null; ) {
    if (t.tag === 3) {
      xs(t, e, n);
      break;
    } else if (t.tag === 1) {
      var r = t.stateNode;
      if (typeof t.type.getDerivedStateFromError == "function" || typeof r.componentDidCatch == "function" && (Pt === null || !Pt.has(r))) {
        e = Tn(n, e), e = mc(t, e, 1), t = Tt(t, e, 1), e = Se(), t !== null && (zr(t, 1, e), Te(t, e));
        break;
      }
    }
    t = t.return;
  }
}
function hp(e, t, n) {
  var r = e.pingCache;
  r !== null && r.delete(t), t = Se(), e.pingedLanes |= e.suspendedLanes & n, fe === e && (me & n) === n && (ue === 4 || ue === 3 && (me & 130023424) === me && 500 > re() - nu ? Gt(e, 0) : tu |= n), Te(e, t);
}
function Wc(e, t) {
  t === 0 && (e.mode & 1 ? (t = Wr, Wr <<= 1, !(Wr & 130023424) && (Wr = 4194304)) : t = 1);
  var n = Se();
  e = mt(e, t), e !== null && (zr(e, t, n), Te(e, n));
}
function vp(e) {
  var t = e.memoizedState, n = 0;
  t !== null && (n = t.retryLane), Wc(e, n);
}
function gp(e, t) {
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
  r !== null && r.delete(t), Wc(e, n);
}
var Oc;
Oc = function(e, t, n) {
  if (e !== null) if (e.memoizedProps !== t.pendingProps || Ce.current) Ne = !0;
  else {
    if (!(e.lanes & n) && !(t.flags & 128)) return Ne = !1, lp(e, t, n);
    Ne = !!(e.flags & 131072);
  }
  else Ne = !1, Y && t.flags & 1048576 && Da(t, yl, t.index);
  switch (t.lanes = 0, t.tag) {
    case 2:
      var r = t.type;
      tl(e, t), e = t.pendingProps;
      var l = En(t, we.current);
      _n(t, n), l = Xo(null, t, r, e, l, n);
      var i = Zo();
      return t.flags |= 1, typeof l == "object" && l !== null && typeof l.render == "function" && l.$$typeof === void 0 ? (t.tag = 1, t.memoizedState = null, t.updateQueue = null, je(r) ? (i = !0, vl(t)) : i = !1, t.memoizedState = l.state !== null && l.state !== void 0 ? l.state : null, Ko(t), l.updater = Il, t.stateNode = l, l._reactInternals = t, eo(t, r, e, n), t = ro(null, t, r, !0, i, n)) : (t.tag = 0, Y && i && Ao(t), ke(null, t, l, n), t = t.child), t;
    case 16:
      r = t.elementType;
      e: {
        switch (tl(e, t), e = t.pendingProps, l = r._init, r = l(r._payload), t.type = r, l = t.tag = wp(r), e = Ge(r, e), l) {
          case 0:
            t = no(null, t, r, e, n);
            break e;
          case 1:
            t = ps(null, t, r, e, n);
            break e;
          case 11:
            t = ds(null, t, r, e, n);
            break e;
          case 14:
            t = fs(null, t, r, Ge(r.type, e), n);
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
      return r = t.type, l = t.pendingProps, l = t.elementType === r ? l : Ge(r, l), no(e, t, r, l, n);
    case 1:
      return r = t.type, l = t.pendingProps, l = t.elementType === r ? l : Ge(r, l), ps(e, t, r, l, n);
    case 3:
      e: {
        if (yc(t), e === null) throw Error(w(387));
        r = t.pendingProps, i = t.memoizedState, l = i.element, Ha(e, t), Sl(t, r, null, n);
        var o = t.memoizedState;
        if (r = o.element, i.isDehydrated) if (i = { element: r, isDehydrated: !1, cache: o.cache, pendingSuspenseBoundaries: o.pendingSuspenseBoundaries, transitions: o.transitions }, t.updateQueue.baseState = i, t.memoizedState = i, t.flags & 256) {
          l = Tn(Error(w(423)), t), t = ms(e, t, r, n, l);
          break e;
        } else if (r !== l) {
          l = Tn(Error(w(424)), t), t = ms(e, t, r, n, l);
          break e;
        } else for (Me = jt(t.stateNode.containerInfo.firstChild), We = t, Y = !0, Xe = null, n = Ba(t, null, r, n), t.child = n; n; ) n.flags = n.flags & -3 | 4096, n = n.sibling;
        else {
          if (Nn(), r === l) {
            t = ht(e, t, n);
            break e;
          }
          ke(e, t, r, n);
        }
        t = t.child;
      }
      return t;
    case 5:
      return Qa(t), e === null && Ji(t), r = t.type, l = t.pendingProps, i = e !== null ? e.memoizedProps : null, o = l.children, Qi(r, l) ? o = null : i !== null && Qi(r, i) && (t.flags |= 32), gc(e, t), ke(e, t, o, n), t.child;
    case 6:
      return e === null && Ji(t), null;
    case 13:
      return wc(e, t, n);
    case 4:
      return Ho(t, t.stateNode.containerInfo), r = t.pendingProps, e === null ? t.child = Cn(t, null, r, n) : ke(e, t, r, n), t.child;
    case 11:
      return r = t.type, l = t.pendingProps, l = t.elementType === r ? l : Ge(r, l), ds(e, t, r, l, n);
    case 7:
      return ke(e, t, t.pendingProps, n), t.child;
    case 8:
      return ke(e, t, t.pendingProps.children, n), t.child;
    case 12:
      return ke(e, t, t.pendingProps.children, n), t.child;
    case 10:
      e: {
        if (r = t.type._context, l = t.pendingProps, i = t.memoizedProps, o = l.value, H(wl, r._currentValue), r._currentValue = o, i !== null) if (qe(i.value, o)) {
          if (i.children === l.children && !Ce.current) {
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
                i.lanes |= n, s = i.alternate, s !== null && (s.lanes |= n), qi(
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
            o.lanes |= n, u = o.alternate, u !== null && (u.lanes |= n), qi(o, n, t), o = i.sibling;
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
        ke(e, t, l.children, n), t = t.child;
      }
      return t;
    case 9:
      return l = t.type, r = t.pendingProps.children, _n(t, n), l = Ke(l), r = r(l), t.flags |= 1, ke(e, t, r, n), t.child;
    case 14:
      return r = t.type, l = Ge(r, t.pendingProps), l = Ge(r.type, l), fs(e, t, r, l, n);
    case 15:
      return hc(e, t, t.type, t.pendingProps, n);
    case 17:
      return r = t.type, l = t.pendingProps, l = t.elementType === r ? l : Ge(r, l), tl(e, t), t.tag = 1, je(r) ? (e = !0, vl(t)) : e = !1, _n(t, n), fc(t, r, l), eo(t, r, l, n), ro(null, t, r, !0, e, n);
    case 19:
      return kc(e, t, n);
    case 22:
      return vc(e, t, n);
  }
  throw Error(w(156, t.tag));
};
function Rc(e, t) {
  return aa(e, t);
}
function yp(e, t, n, r) {
  this.tag = e, this.key = n, this.sibling = this.child = this.return = this.stateNode = this.type = this.elementType = null, this.index = 0, this.ref = null, this.pendingProps = t, this.dependencies = this.memoizedState = this.updateQueue = this.memoizedProps = null, this.mode = r, this.subtreeFlags = this.flags = 0, this.deletions = null, this.childLanes = this.lanes = 0, this.alternate = null;
}
function Ve(e, t, n, r) {
  return new yp(e, t, n, r);
}
function ou(e) {
  return e = e.prototype, !(!e || !e.isReactComponent);
}
function wp(e) {
  if (typeof e == "function") return ou(e) ? 1 : 0;
  if (e != null) {
    if (e = e.$$typeof, e === Eo) return 11;
    if (e === No) return 14;
  }
  return 2;
}
function Mt(e, t) {
  var n = e.alternate;
  return n === null ? (n = Ve(e.tag, t, e.key, e.mode), n.elementType = e.elementType, n.type = e.type, n.stateNode = e.stateNode, n.alternate = e, e.alternate = n) : (n.pendingProps = t, n.type = e.type, n.flags = 0, n.subtreeFlags = 0, n.deletions = null), n.flags = e.flags & 14680064, n.childLanes = e.childLanes, n.lanes = e.lanes, n.child = e.child, n.memoizedProps = e.memoizedProps, n.memoizedState = e.memoizedState, n.updateQueue = e.updateQueue, t = e.dependencies, n.dependencies = t === null ? null : { lanes: t.lanes, firstContext: t.firstContext }, n.sibling = e.sibling, n.index = e.index, n.ref = e.ref, n;
}
function ll(e, t, n, r, l, i) {
  var o = 2;
  if (r = e, typeof e == "function") ou(e) && (o = 1);
  else if (typeof e == "string") o = 5;
  else e: switch (e) {
    case un:
      return Yt(n.children, l, i, t);
    case zo:
      o = 8, l |= 8;
      break;
    case zi:
      return e = Ve(12, n, t, l | 2), e.elementType = zi, e.lanes = i, e;
    case Ei:
      return e = Ve(13, n, t, l), e.elementType = Ei, e.lanes = i, e;
    case Ni:
      return e = Ve(19, n, t, l), e.elementType = Ni, e.lanes = i, e;
    case Qs:
      return $l(n, l, i, t);
    default:
      if (typeof e == "object" && e !== null) switch (e.$$typeof) {
        case Ks:
          o = 10;
          break e;
        case Hs:
          o = 9;
          break e;
        case Eo:
          o = 11;
          break e;
        case No:
          o = 14;
          break e;
        case wt:
          o = 16, r = null;
          break e;
      }
      throw Error(w(130, e == null ? e : typeof e, ""));
  }
  return t = Ve(o, n, t, l), t.elementType = e, t.type = r, t.lanes = i, t;
}
function Yt(e, t, n, r) {
  return e = Ve(7, e, r, t), e.lanes = n, e;
}
function $l(e, t, n, r) {
  return e = Ve(22, e, r, t), e.elementType = Qs, e.lanes = n, e.stateNode = { isHidden: !1 }, e;
}
function yi(e, t, n) {
  return e = Ve(6, e, null, t), e.lanes = n, e;
}
function wi(e, t, n) {
  return t = Ve(4, e.children !== null ? e.children : [], e.key, t), t.lanes = n, t.stateNode = { containerInfo: e.containerInfo, pendingChildren: null, implementation: e.implementation }, t;
}
function kp(e, t, n, r, l) {
  this.tag = t, this.containerInfo = e, this.finishedWork = this.pingCache = this.current = this.pendingChildren = null, this.timeoutHandle = -1, this.callbackNode = this.pendingContext = this.context = null, this.callbackPriority = 0, this.eventTimes = bl(0), this.expirationTimes = bl(-1), this.entangledLanes = this.finishedLanes = this.mutableReadLanes = this.expiredLanes = this.pingedLanes = this.suspendedLanes = this.pendingLanes = 0, this.entanglements = bl(0), this.identifierPrefix = r, this.onRecoverableError = l, this.mutableSourceEagerHydrationData = null;
}
function uu(e, t, n, r, l, i, o, u, s) {
  return e = new kp(e, t, n, u, s), t === 1 ? (t = 1, i === !0 && (t |= 8)) : t = 0, i = Ve(3, null, null, t), e.current = i, i.stateNode = e, i.memoizedState = { element: r, isDehydrated: n, cache: null, transitions: null, pendingSuspenseBoundaries: null }, Ko(i), e;
}
function Sp(e, t, n) {
  var r = 3 < arguments.length && arguments[3] !== void 0 ? arguments[3] : null;
  return { $$typeof: on, key: r == null ? null : "" + r, children: e, containerInfo: t, implementation: n };
}
function Ac(e) {
  if (!e) return Wt;
  e = e._reactInternals;
  e: {
    if (tn(e) !== e || e.tag !== 1) throw Error(w(170));
    var t = e;
    do {
      switch (t.tag) {
        case 3:
          t = t.stateNode.context;
          break e;
        case 1:
          if (je(t.type)) {
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
    if (je(n)) return Aa(e, n, t);
  }
  return t;
}
function Ic(e, t, n, r, l, i, o, u, s) {
  return e = uu(n, r, !0, e, l, i, o, u, s), e.context = Ac(null), n = e.current, r = Se(), l = Lt(n), i = dt(r, l), i.callback = t ?? null, Tt(n, i, l), e.current.lanes = l, zr(e, l, r), Te(e, r), e;
}
function Vl(e, t, n, r) {
  var l = t.current, i = Se(), o = Lt(l);
  return n = Ac(n), t.context === null ? t.context = n : t.pendingContext = n, t = dt(i, o), t.payload = { element: e }, r = r === void 0 ? null : r, r !== null && (t.callback = r), e = Tt(l, t, o), e !== null && (Je(e, l, o, i), qr(e, l, o)), o;
}
function Tl(e) {
  if (e = e.current, !e.child) return null;
  switch (e.child.tag) {
    case 5:
      return e.child.stateNode;
    default:
      return e.child.stateNode;
  }
}
function zs(e, t) {
  if (e = e.memoizedState, e !== null && e.dehydrated !== null) {
    var n = e.retryLane;
    e.retryLane = n !== 0 && n < t ? n : t;
  }
}
function su(e, t) {
  zs(e, t), (e = e.alternate) && zs(e, t);
}
function _p() {
  return null;
}
var Dc = typeof reportError == "function" ? reportError : function(e) {
  console.error(e);
};
function au(e) {
  this._internalRoot = e;
}
Bl.prototype.render = au.prototype.render = function(e) {
  var t = this._internalRoot;
  if (t === null) throw Error(w(409));
  Vl(e, t, null, null);
};
Bl.prototype.unmount = au.prototype.unmount = function() {
  var e = this._internalRoot;
  if (e !== null) {
    this._internalRoot = null;
    var t = e.containerInfo;
    bt(function() {
      Vl(null, e, null, null);
    }), t[pt] = null;
  }
};
function Bl(e) {
  this._internalRoot = e;
}
Bl.prototype.unstable_scheduleHydration = function(e) {
  if (e) {
    var t = va();
    e = { blockedOn: null, target: e, priority: t };
    for (var n = 0; n < St.length && t !== 0 && t < St[n].priority; n++) ;
    St.splice(n, 0, e), n === 0 && ya(e);
  }
};
function cu(e) {
  return !(!e || e.nodeType !== 1 && e.nodeType !== 9 && e.nodeType !== 11);
}
function Kl(e) {
  return !(!e || e.nodeType !== 1 && e.nodeType !== 9 && e.nodeType !== 11 && (e.nodeType !== 8 || e.nodeValue !== " react-mount-point-unstable "));
}
function Es() {
}
function xp(e, t, n, r, l) {
  if (l) {
    if (typeof r == "function") {
      var i = r;
      r = function() {
        var d = Tl(o);
        i.call(d);
      };
    }
    var o = Ic(t, r, e, 0, null, !1, !1, "", Es);
    return e._reactRootContainer = o, e[pt] = o.current, mr(e.nodeType === 8 ? e.parentNode : e), bt(), o;
  }
  for (; l = e.lastChild; ) e.removeChild(l);
  if (typeof r == "function") {
    var u = r;
    r = function() {
      var d = Tl(s);
      u.call(d);
    };
  }
  var s = uu(e, 0, !1, null, null, !1, !1, "", Es);
  return e._reactRootContainer = s, e[pt] = s.current, mr(e.nodeType === 8 ? e.parentNode : e), bt(function() {
    Vl(t, s, n, r);
  }), s;
}
function Hl(e, t, n, r, l) {
  var i = n._reactRootContainer;
  if (i) {
    var o = i;
    if (typeof l == "function") {
      var u = l;
      l = function() {
        var s = Tl(o);
        u.call(s);
      };
    }
    Vl(t, o, e, l);
  } else o = xp(n, t, e, l, r);
  return Tl(o);
}
ma = function(e) {
  switch (e.tag) {
    case 3:
      var t = e.stateNode;
      if (t.current.memoizedState.isDehydrated) {
        var n = Yn(t.pendingLanes);
        n !== 0 && (To(t, n | 1), Te(t, re()), !(U & 6) && (Pn = re() + 500, At()));
      }
      break;
    case 13:
      bt(function() {
        var r = mt(e, 1);
        if (r !== null) {
          var l = Se();
          Je(r, e, 1, l);
        }
      }), su(e, 1);
  }
};
Po = function(e) {
  if (e.tag === 13) {
    var t = mt(e, 134217728);
    if (t !== null) {
      var n = Se();
      Je(t, e, 134217728, n);
    }
    su(e, 134217728);
  }
};
ha = function(e) {
  if (e.tag === 13) {
    var t = Lt(e), n = mt(e, t);
    if (n !== null) {
      var r = Se();
      Je(n, e, t, r);
    }
    su(e, t);
  }
};
va = function() {
  return $;
};
ga = function(e, t) {
  var n = $;
  try {
    return $ = e, t();
  } finally {
    $ = n;
  }
};
Ri = function(e, t, n) {
  switch (t) {
    case "input":
      if (Ti(e, n), t = n.name, n.type === "radio" && t != null) {
        for (n = e; n.parentNode; ) n = n.parentNode;
        for (n = n.querySelectorAll("input[name=" + JSON.stringify("" + t) + '][type="radio"]'), t = 0; t < n.length; t++) {
          var r = n[t];
          if (r !== e && r.form === e.form) {
            var l = Ol(r);
            if (!l) throw Error(w(90));
            Ys(r), Ti(r, l);
          }
        }
      }
      break;
    case "textarea":
      Zs(e, n);
      break;
    case "select":
      t = n.value, t != null && yn(e, !!n.multiple, t, !1);
  }
};
ra = ru;
la = bt;
var zp = { usingClientEntryPoint: !1, Events: [Nr, dn, Ol, ta, na, ru] }, Kn = { findFiberByHostInstance: Kt, bundleType: 0, version: "18.3.1", rendererPackageName: "react-dom" }, Ep = { bundleType: Kn.bundleType, version: Kn.version, rendererPackageName: Kn.rendererPackageName, rendererConfig: Kn.rendererConfig, overrideHookState: null, overrideHookStateDeletePath: null, overrideHookStateRenamePath: null, overrideProps: null, overridePropsDeletePath: null, overridePropsRenamePath: null, setErrorHandler: null, setSuspenseHandler: null, scheduleUpdate: null, currentDispatcherRef: vt.ReactCurrentDispatcher, findHostInstanceByFiber: function(e) {
  return e = ua(e), e === null ? null : e.stateNode;
}, findFiberByHostInstance: Kn.findFiberByHostInstance || _p, findHostInstancesForRefresh: null, scheduleRefresh: null, scheduleRoot: null, setRefreshHandler: null, getCurrentFiber: null, reconcilerVersion: "18.3.1-next-f1338f8080-20240426" };
if (typeof __REACT_DEVTOOLS_GLOBAL_HOOK__ < "u") {
  var Hr = __REACT_DEVTOOLS_GLOBAL_HOOK__;
  if (!Hr.isDisabled && Hr.supportsFiber) try {
    Ll = Hr.inject(Ep), nt = Hr;
  } catch {
  }
}
Re.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED = zp;
Re.createPortal = function(e, t) {
  var n = 2 < arguments.length && arguments[2] !== void 0 ? arguments[2] : null;
  if (!cu(t)) throw Error(w(200));
  return Sp(e, t, null, n);
};
Re.createRoot = function(e, t) {
  if (!cu(e)) throw Error(w(299));
  var n = !1, r = "", l = Dc;
  return t != null && (t.unstable_strictMode === !0 && (n = !0), t.identifierPrefix !== void 0 && (r = t.identifierPrefix), t.onRecoverableError !== void 0 && (l = t.onRecoverableError)), t = uu(e, 1, !1, null, null, n, !1, r, l), e[pt] = t.current, mr(e.nodeType === 8 ? e.parentNode : e), new au(t);
};
Re.findDOMNode = function(e) {
  if (e == null) return null;
  if (e.nodeType === 1) return e;
  var t = e._reactInternals;
  if (t === void 0)
    throw typeof e.render == "function" ? Error(w(188)) : (e = Object.keys(e).join(","), Error(w(268, e)));
  return e = ua(t), e = e === null ? null : e.stateNode, e;
};
Re.flushSync = function(e) {
  return bt(e);
};
Re.hydrate = function(e, t, n) {
  if (!Kl(t)) throw Error(w(200));
  return Hl(null, e, t, !0, n);
};
Re.hydrateRoot = function(e, t, n) {
  if (!cu(e)) throw Error(w(405));
  var r = n != null && n.hydratedSources || null, l = !1, i = "", o = Dc;
  if (n != null && (n.unstable_strictMode === !0 && (l = !0), n.identifierPrefix !== void 0 && (i = n.identifierPrefix), n.onRecoverableError !== void 0 && (o = n.onRecoverableError)), t = Ic(t, null, e, 1, n ?? null, l, !1, i, o), e[pt] = t.current, mr(e), r) for (e = 0; e < r.length; e++) n = r[e], l = n._getVersion, l = l(n._source), t.mutableSourceEagerHydrationData == null ? t.mutableSourceEagerHydrationData = [n, l] : t.mutableSourceEagerHydrationData.push(
    n,
    l
  );
  return new Bl(t);
};
Re.render = function(e, t, n) {
  if (!Kl(t)) throw Error(w(200));
  return Hl(null, e, t, !1, n);
};
Re.unmountComponentAtNode = function(e) {
  if (!Kl(e)) throw Error(w(40));
  return e._reactRootContainer ? (bt(function() {
    Hl(null, null, e, !1, function() {
      e._reactRootContainer = null, e[pt] = null;
    });
  }), !0) : !1;
};
Re.unstable_batchedUpdates = ru;
Re.unstable_renderSubtreeIntoContainer = function(e, t, n, r) {
  if (!Kl(n)) throw Error(w(200));
  if (e == null || e._reactInternals === void 0) throw Error(w(38));
  return Hl(e, t, n, !1, r);
};
Re.version = "18.3.1-next-f1338f8080-20240426";
function Uc() {
  if (!(typeof __REACT_DEVTOOLS_GLOBAL_HOOK__ > "u" || typeof __REACT_DEVTOOLS_GLOBAL_HOOK__.checkDCE != "function"))
    try {
      __REACT_DEVTOOLS_GLOBAL_HOOK__.checkDCE(Uc);
    } catch (e) {
      console.error(e);
    }
}
Uc(), Us.exports = Re;
var Np = Us.exports, $c, Ns = Np;
$c = Ns.createRoot, Ns.hydrateRoot;
function du(e) {
  return e.replace(/\/+$/, "");
}
async function fu(e, t) {
  let n = "";
  try {
    n = await e.text();
  } catch {
    n = "";
  }
  const r = new Error(`${t} failed with ${e.status}${n ? `: ${n.slice(0, 500)}` : ""}`);
  return r.status = e.status, r.body = n, r;
}
function Fe(e) {
  return e && typeof e == "object" && !Array.isArray(e) ? e : {};
}
function Cp(e) {
  const t = Fe(e), n = [
    t.config,
    t.client,
    t.data,
    t.result,
    t.item,
    t
  ];
  for (const r of n) {
    const l = Fe(r);
    if (Object.keys(l).length > 0) return l;
  }
  return t;
}
function A(e, t = "") {
  return e == null ? t : String(e);
}
function pu(e, t = 0) {
  const n = Number(e);
  return Number.isFinite(n) ? n : t;
}
function vo(e, t = !1) {
  return typeof e == "boolean" ? e : typeof e == "number" ? e === 1 : typeof e == "string" ? ["1", "true", "yes", "ja", "on"].includes(e.toLowerCase()) : t;
}
function lt(e) {
  if (Array.isArray(e)) return e;
  if (typeof e == "string") {
    const t = e.trim();
    if (!t) return [];
    try {
      const n = JSON.parse(t), r = lt(n);
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
      const l = lt(t[r]);
      if (l.length > 0) return l;
    }
  }
  return [];
}
function Hn(e, t) {
  for (const n of t) {
    const r = lt(e[n]);
    if (r.length > 0) return r;
  }
  return [];
}
function jp(e) {
  const t = [];
  for (const n of lt(e)) {
    const r = Fe(n), l = pu(r.id, NaN), i = A(r.name ?? r.title, "");
    !Number.isFinite(l) || !i || t.push({
      id: l,
      name: i,
      slug: typeof r.slug == "string" ? r.slug : void 0,
      icon: typeof r.icon == "string" ? r.icon : void 0
    });
  }
  return t;
}
function Tp(e) {
  const t = Fe(e), n = A(t.value ?? t.slug ?? (t.id !== void 0 ? String(t.id) : ""), "").trim(), r = A(t.title ?? t.name, "").trim();
  if (!n || !r) return;
  const l = t.numericValue ?? t.numeric_value, i = l != null ? pu(l, NaN) : NaN;
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
function Pp(e) {
  const t = Fe(e), n = A(t.answerKey ?? t.answer_key, "").trim(), r = A(t.title, "").trim();
  if (!n || !r) return;
  const l = A(t.questionType ?? t.question_type, "generic"), i = A(t.fieldType ?? t.field_type, "").trim(), o = lt(t.options).map(Tp).filter((u) => !!u);
  return {
    answerKey: n,
    title: r,
    helpText: A(t.helpText ?? t.help_text, "").trim() || null,
    questionType: l,
    multiSelect: vo(t.multiSelect ?? t.multi_select, !1),
    fieldType: i || null,
    required: vo(t.required, !1),
    dependsOnAnswerKey: A(t.dependsOnAnswerKey ?? t.depends_on_answer_key, "").trim() || null,
    dependsOnValue: A(t.dependsOnValue ?? t.depends_on_value, "").trim() || null,
    options: o
  };
}
function Lp(e) {
  const t = [];
  for (const n of lt(e)) {
    const r = Fe(n), l = A(r.key ?? r.stepKey ?? r.step_key, "").trim(), i = A(r.label ?? r.title, "").trim();
    !l || !i || t.push({
      key: l,
      label: i,
      type: A(r.type, "questions"),
      questions: lt(r.questions).map(Pp).filter((o) => !!o)
    });
  }
  return t;
}
async function Mp(e, t) {
  if (!e) {
    const u = new Error("No apiBaseUrl configured");
    throw u.status = 0, u;
  }
  const n = `${du(e)}/api/tfd-partner-wizard/config/${encodeURIComponent(t)}`, r = await fetch(n, {
    method: "GET",
    credentials: "omit",
    headers: {
      Accept: "application/json"
    }
  });
  if (!r.ok)
    throw await fu(r, "Config request");
  const l = await r.json(), i = Cp(l), o = A(i.intro ?? i.introText ?? i.description, "Finde heraus, welches Partner-Modell und welche Tool-Richtung zu dir passt.");
  return {
    clientId: A(i.clientId ?? i.client_id ?? t, t),
    name: A(i.name, ""),
    headline: A(i.headline ?? i.title, "Chiptuning Händler werden"),
    intro: o,
    introText: o,
    primaryColor: A(i.primaryColor ?? i.primary_color, "#E31E24"),
    theme: A(i.theme, "light"),
    toolRequired: vo(i.toolRequired ?? i.tool_required, !0),
    defaultVehiclePrice: pu(i.defaultVehiclePrice ?? i.default_vehicle_price, 0),
    enabledVehicleCategories: jp(
      Hn(i, ["enabledVehicleCategories", "enabled_vehicle_categories", "vehicleCategories", "vehicle_categories", "vehicles"])
    ),
    enabledServices: Hn(i, ["enabledServices", "enabled_services", "services"]),
    currentToolOptions: Hn(i, ["currentToolOptions", "current_tool_options", "toolOptions", "tool_options", "currentTools", "current_tools"]),
    customLabels: i.customLabels ?? i.custom_labels ?? {},
    privacyNotice: A(
      i.privacyNotice ?? i.privacy_notice,
      "Mit dem Absenden erklärst du dich einverstanden, dass deine Angaben zur Bearbeitung der Anfrage gespeichert werden und wir dich kontaktieren."
    ),
    toolDisclaimer: A(i.toolDisclaimer ?? i.tool_disclaimer, ""),
    ctaLabel: A(i.ctaLabel ?? i.cta_label, "Anfrage senden"),
    contactTarget: A(i.contactTarget ?? i.contact_target, ""),
    tools: Hn(i, ["tools", "toolRecommendations", "tool_recommendations", "recommendedTools"]),
    packages: Hn(i, ["packages", "partnerPackages", "partner_packages"]),
    steps: Lp(i.steps),
    raw: l
  };
}
async function Fp(e, t) {
  if (!e)
    throw new Error("No apiBaseUrl configured");
  const n = `${du(e)}/api/tfd-partner-wizard/recommend`, r = await fetch(n, {
    method: "POST",
    credentials: "omit",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json"
    },
    body: JSON.stringify(t)
  });
  if (!r.ok)
    throw await fu(r, "Recommendation request");
  const l = await r.json();
  return Ip(l);
}
async function Wp(e, t) {
  if (!e)
    throw new Error("No apiBaseUrl configured");
  const n = `${du(e)}/api/tfd-partner-wizard/leads`, r = await fetch(n, {
    method: "POST",
    credentials: "omit",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json"
    },
    body: JSON.stringify(t)
  });
  if (!r.ok)
    throw await fu(r, "Lead request");
  try {
    return await r.json();
  } catch {
    return null;
  }
}
function Op(e) {
  return lt(e).map((t) => {
    if (typeof t == "string") return t.trim();
    const n = Fe(t);
    return A(n.name ?? n.title ?? n.label ?? n.text ?? n.description).trim();
  }).filter(Boolean);
}
function Vc(e) {
  const t = Fe(e), n = A(t.title).trim();
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
function Rp(e) {
  const t = [];
  return lt(e).forEach((n) => {
    const r = Vc(n);
    r && t.push(r);
  }), t;
}
function Ap(e) {
  const t = [];
  return lt(e).forEach((n, r) => {
    if (typeof n == "string") {
      const s = n.trim();
      s && t.push({ key: `tool-${r}`, name: s });
      return;
    }
    const l = Fe(n), i = A(l.name ?? l.title ?? l.label).trim();
    if (!i) return;
    const o = Vc(l.product), u = A(l.imageUrl ?? l.image_url).trim() || (o == null ? void 0 : o.imageUrl);
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
function Ip(e) {
  const t = Fe(e), n = Fe(t.recommendation), r = Fe(t.result), l = Object.keys(n).length > 0 ? n : Object.keys(r).length > 0 ? r : t, i = Fe(l.package ?? l.recommendedPackage ?? l.packageData ?? l.recommended_package), o = l.packageName ?? l.packageTitle ?? l.package_title ?? l.recommendedPackageTitle ?? l.recommended_package_title ?? l.title ?? l.name ?? i.name ?? i.title ?? i.packageTitle ?? "Partner Empfehlung", u = l.packageKey ?? l.package_key ?? l.key ?? l.slug ?? i.key ?? i.slug ?? A(o).toLowerCase().replace(/[^a-z0-9]+/gi, "-"), s = Op(
    l.bullets ?? l.items ?? l.benefits ?? l.packageFeatures ?? l.package_features ?? l.features ?? i.bullets ?? i.benefits ?? i.features ?? []
  ), d = Ap(
    l.recommendedTools ?? l.recommended_tools ?? l.tools ?? l.toolRecommendations ?? l.tool_recommendations ?? []
  ), m = Rp(
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
const ki = {
  clientId: "no1",
  headline: "Chiptuning Händler werden",
  intro: "Finde heraus, welches Partner-Modell und welche Tool-Richtung zu dir passt.",
  privacyNotice: "Mit dem Absenden erklärst du dich einverstanden, dass deine Angaben zur Bearbeitung deiner Anfrage gespeichert werden.",
  ctaLabel: "Anfrage senden"
}, Dp = [
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
], Up = [
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
], $p = [
  { value: "pkw", title: "PKW", text: "Standard-Fahrzeuge, Performance und Daily Driver." },
  { value: "lkw", title: "LKW", text: "Nutzfahrzeuge, Flotten und Speditionen." },
  { value: "motorrad", title: "Motorrad", text: "Bike-Tuning und Performance-Anwendungen." },
  { value: "agrar", title: "Agrar", text: "Traktoren, Landmaschinen und Eco-Anwendungen." },
  { value: "atv", title: "Quad", text: "ATV, Quad und Side-by-Side." },
  { value: "boat", title: "Boot", text: "Boote und Marine-Anwendungen." },
  { value: "jetski", title: "Jetski", text: "JetSki und Wassersport-Fahrzeuge." }
], Bc = [
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
], Vp = [
  { value: "stage1", title: "Stage 1", text: "Leistungsoptimierung für Kundenfahrzeuge." },
  { value: "stage2", title: "Stage 2", text: "Optimierung mit Hardware-Anpassungen." },
  { value: "eco", title: "Eco-Tuning", text: "Verbrauchsoptimierung für Flotten und Gewerbe." },
  { value: "fileService", title: "File Service", text: "Files lesen, prüfen lassen und schreiben." },
  { value: "diagnostic", title: "Diagnose Support", text: "Technische Rückfragen, Fehlerbilder und Fälle." },
  { value: "special", title: "Sonderoptionen", text: "VMax, DSG, Start-Stop und weitere Optionen." }
], Bp = [
  { value: "low", title: "1–5 Fahrzeuge / Monat", text: "Kleiner Start oder gelegentliche Anfragen." },
  { value: "medium", title: "6–20 Fahrzeuge / Monat", text: "Regelmäßiges Geschäft mit Wachstum." },
  { value: "high", title: "20+ Fahrzeuge / Monat", text: "Professioneller Betrieb mit Volumen." },
  { value: "network", title: "Händler / Netzwerk", text: "Mehrere Standorte, Slaves oder Partner." }
];
function Kp(e, t) {
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
const Hp = { low: 3, medium: 12, high: 25, network: 40 }, Qp = [
  {
    key: "entry",
    label: "Einstieg",
    type: "questions",
    questions: [
      { answerKey: "goal", title: "Was ist dein Ziel?", questionType: "generic", multiSelect: !1, options: Dp },
      { answerKey: "experience", title: "Wie viel Erfahrung hast du?", questionType: "generic", multiSelect: !1, options: Up }
    ]
  },
  {
    key: "vehicleTool",
    label: "Fahrzeug & Tool",
    type: "questions",
    questions: [
      { answerKey: "vehicles", title: "Welche Fahrzeuge möchtest du bearbeiten?", questionType: "vehicleType", multiSelect: !0, options: $p },
      {
        answerKey: "tool",
        title: "Hast du bereits ein eigenes Tool?",
        questionType: "toolManufacturer",
        multiSelect: !1,
        helpText: "Ohne eigenes Tuning-Tool ist keine direkte Händler-Aktivierung möglich. Dann geht es zuerst in die Tool-Beratung.",
        options: Bc
      }
    ]
  },
  {
    key: "offerVolume",
    label: "Angebot & Volumen",
    type: "questions",
    questions: [
      { answerKey: "services", title: "Welche Leistungen willst du anbieten?", questionType: "generic", multiSelect: !0, options: Vp },
      {
        answerKey: "volume",
        title: "Wie viele Fahrzeuge erwartest du?",
        questionType: "volume",
        multiSelect: !1,
        options: Bp.map((e) => ({ ...e, numericValue: Hp[e.value] }))
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
function Gp(e) {
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
function il(e, t) {
  return e[t];
}
function Cs(e, t, n) {
  return { ...e, [t]: n };
}
function Zn(e, t) {
  var n;
  return t === "privacyAccepted" ? e.privacyAccepted : (n = e.contact) == null ? void 0 : n[t];
}
function Qr(e, t) {
  const n = e.dependsOnAnswerKey, r = e.dependsOnValue;
  if (!n || !r) return !0;
  const l = il(t, n) ?? Zn(t, n);
  return Array.isArray(l) ? l.includes(r) : String(l ?? "") === r;
}
const Yp = [
  "Fahrzeugdatenbank API für 1 Jahr mit drin"
];
function Si(e, t) {
  const n = e ?? [];
  return n.includes(t) ? n.filter((r) => r !== t) : [...n, t];
}
function Xp(e) {
  var t;
  return !!((t = e.vehicles) != null && t.length && e.tool);
}
function De(e) {
  if (!e) return;
  const t = Number(e);
  if (Number.isFinite(t))
    return `${t.toLocaleString("de-DE", { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €*`;
}
function ln(e) {
  const t = Number(e == null ? void 0 : e.specialPrice);
  return Number.isFinite(t) && t > 0;
}
function $t(e) {
  return ln(e) ? e == null ? void 0 : e.specialPrice : e == null ? void 0 : e.price;
}
function Zp(e, t) {
  if (!e) return 0;
  const n = t.find((r) => r.value === e);
  return (n == null ? void 0 : n.numericValue) ?? 0;
}
function Jp(e) {
  return !e || e === "none" || e === "other" ? "unknown" : "slave";
}
function js(e, t, n, r, l) {
  const i = t.contact ?? {}, o = t.vehicles ?? [], u = o.map((h) => n[h]).filter((h) => typeof h == "number"), s = t.services ?? [], d = t.tool ?? "", m = Zp(t.volume, r), v = Jp(t.tool);
  return {
    clientId: e,
    contact: i,
    answers: t,
    recommendation: l,
    source: "no1_partner_wizard",
    selectedTools: t.selectedTools ?? [],
    // New Symfony recommendation service fields (VehicleType IDs, not slugs).
    currentTool: d,
    vehicleCategories: u,
    mainVehicleCategory: u[0] ?? "",
    services: s,
    goal: t.goal ?? "",
    experience: t.experience ?? "",
    monthlyVehicleCount: m,
    desiredToolLevel: v,
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
    tool: d,
    volume: t.volume,
    payload: {
      clientId: e,
      contact: i,
      answers: t,
      recommendation: l,
      currentTool: d,
      vehicleCategories: u,
      mainVehicleCategory: u[0] ?? "",
      services: s,
      monthlyVehicleCount: m,
      desiredToolLevel: v,
      privacyAccepted: t.privacyAccepted === !0,
      source: "no1_partner_wizard"
    }
  };
}
function _i() {
  return /* @__PURE__ */ a.jsxs("svg", { viewBox: "0 0 20 20", fill: "none", xmlns: "http://www.w3.org/2000/svg", "aria-hidden": "true", children: [
    /* @__PURE__ */ a.jsx("circle", { cx: "10", cy: "10", r: "9", fill: "currentColor", opacity: "0.15" }),
    /* @__PURE__ */ a.jsx("path", { d: "M6 10.5L8.5 13L14 7", stroke: "currentColor", strokeWidth: "2", strokeLinecap: "round", strokeLinejoin: "round" })
  ] });
}
function qp() {
  return /* @__PURE__ */ a.jsxs("svg", { viewBox: "0 0 20 20", fill: "none", xmlns: "http://www.w3.org/2000/svg", "aria-hidden": "true", children: [
    /* @__PURE__ */ a.jsx("circle", { cx: "10", cy: "10", r: "9", fill: "currentColor", opacity: "0.15" }),
    /* @__PURE__ */ a.jsx("path", { d: "M7 7L13 13M13 7L7 13", stroke: "currentColor", strokeWidth: "2", strokeLinecap: "round", strokeLinejoin: "round" })
  ] });
}
function bp({
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
function em({
  clientId: e = "no1",
  apiBaseUrl: t = "",
  primaryColor: n = "#E31E24",
  theme: r = "light"
}) {
  const [l, i] = te.useState(ki), [o, u] = te.useState(""), [s, d] = te.useState(0), [m, v] = te.useState({}), [h, _] = te.useState(!1), [x, k] = te.useState(""), [K, f] = te.useState(null), [c, p] = te.useState(""), [y, E] = te.useState(!1), [z, T] = te.useState(null);
  te.useEffect(() => {
    let g = !0;
    return Mp(t, e).then((S) => {
      g && (i({ ...ki, ...S }), u(""));
    }).catch((S) => {
      if (!g) return;
      i(ki);
      const F = typeof S == "object" && S !== null && "status" in S ? String(S.status ?? "offline") : "offline";
      u(`Config konnte nicht geladen werden (${F}). Lokale Fallback-Konfiguration aktiv.`);
    }), () => {
      g = !1;
    };
  }, [t, e]);
  const P = te.useMemo(
    () => Kp(m),
    [m, l]
  ), V = te.useMemo(
    () => l.steps && l.steps.length > 0 ? l.steps : Qp,
    [l]
  ), R = te.useMemo(() => {
    const g = {};
    for (const S of V)
      for (const F of S.questions) {
        const D = F.options.map(Gp);
        if (F.answerKey === "tool") {
          const ce = D.some((L) => L.value === "none");
          g[F.answerKey] = ce ? D : [Bc[0], ...D];
        } else
          g[F.answerKey] = D;
      }
    return g;
  }, [V]), se = R.vehicles ?? [], It = R.services ?? [], Dt = R.tool ?? [], Wn = R.volume ?? [], On = te.useMemo(
    () => Object.fromEntries(
      se.filter((g) => typeof g.id == "number").map((g) => [g.value, g.id])
    ),
    [se]
  );
  te.useEffect(() => {
    if (!Xp(m)) {
      f(null), p("");
      return;
    }
    let g = !0;
    E(!0), p("");
    const S = js(e, m, On, Wn, P);
    return Fp(t, S).then((F) => {
      g && (f(F), p(""));
    }).catch((F) => {
      if (!g) return;
      f(null);
      const D = F instanceof Error ? F.message : "Recommendation API nicht erreichbar.";
      p(`${D} Lokale Empfehlung aktiv.`);
    }).finally(() => {
      g && E(!1);
    }), () => {
      g = !1;
    };
  }, [m, t, e, P, On]);
  const ae = V[s], b = K ?? P, N = te.useMemo(
    () => (b.recommendedTools ?? []).map((g) => g.key),
    [b]
  ), W = Math.round((s + 1) / V.length * 100);
  te.useEffect(() => {
    if (!z) return;
    const g = (S) => {
      S.key === "Escape" && T(null);
    };
    return window.addEventListener("keydown", g), () => window.removeEventListener("keydown", g);
  }, [z]), te.useEffect(() => {
    v((g) => {
      const S = g.selectedTools ?? [];
      if (S.length === 0) return g;
      const F = S.filter((D) => N.includes(D));
      return F.length === S.length ? g : { ...g, selectedTools: F };
    });
  }, [N]);
  const O = te.useMemo(() => ae.type === "recommendation" ? !0 : ae.type === "contact" ? ae.questions.filter((g) => Qr(g, m) && g.required).every((g) => {
    const S = Zn(m, g.answerKey);
    return g.fieldType === "checkbox" ? g.multiSelect ? Array.isArray(S) && S.length > 0 : S === !0 : !!(S && String(S).trim());
  }) : ae.questions.filter((g) => Qr(g, m)).every((g) => {
    const S = il(m, g.answerKey);
    return g.multiSelect ? Array.isArray(S) && S.length > 0 : !!S;
  }), [m, ae]), X = (g, S) => {
    v((F) => Cs(F, g, S)), k("");
  }, le = (g, S) => {
    v((F) => Cs(F, g, Si(il(F, g), S))), k("");
  }, Ut = se.length > 0 && se.every((g) => (m.vehicles ?? []).includes(g.value)), it = () => {
    v((g) => ({
      ...g,
      vehicles: Ut ? [] : se.map((S) => S.value)
    })), k("");
  }, Rn = (g, S, F = !1) => /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__grid", children: g.map((D) => {
    const ce = il(m, S), L = Array.isArray(ce) ? ce.includes(D.value) : ce === D.value;
    return /* @__PURE__ */ a.jsx(
      bp,
      {
        option: D,
        selected: L,
        onClick: () => {
          F ? le(S, D.value) : X(S, D.value);
        }
      },
      D.value
    );
  }) }), ot = () => {
    O && (d((g) => Math.min(g + 1, V.length - 1)), k(""));
  }, nn = () => {
    d((g) => Math.max(g - 1, 0)), k("");
  }, jr = (g, S) => {
    v((F) => ({
      ...F,
      contact: {
        ...F.contact,
        [g]: S
      }
    })), k("");
  }, Kc = (g, S) => {
    v(g === "privacyAccepted" ? (F) => ({ ...F, privacyAccepted: S }) : (F) => ({
      ...F,
      contact: {
        ...F.contact,
        [g]: S ? "true" : ""
      }
    })), k("");
  }, Hc = (g, S) => {
    v((F) => {
      var L;
      const D = (L = F.contact) == null ? void 0 : L[g], ce = Array.isArray(D) ? D : [];
      return {
        ...F,
        contact: {
          ...F.contact,
          [g]: Si(ce, S)
        }
      };
    }), k("");
  }, mu = (g) => {
    v((S) => ({
      ...S,
      selectedTools: Si(S.selectedTools, g)
    })), k("");
  }, Ql = (g) => {
    T(g);
  }, Qc = () => {
    if (!z) return null;
    const g = z.product, S = De($t(g)), F = ln(g) ? De(g == null ? void 0 : g.price) : void 0, D = z.imageUrl ?? (g == null ? void 0 : g.imageUrl), ce = (m.selectedTools ?? []).includes(z.key);
    return /* @__PURE__ */ a.jsx(
      "div",
      {
        className: "tfdWizard__modalBackdrop",
        role: "presentation",
        onClick: () => T(null),
        children: /* @__PURE__ */ a.jsxs(
          "div",
          {
            className: "tfdWizard__productModal",
            role: "dialog",
            "aria-modal": "true",
            "aria-label": (g == null ? void 0 : g.title) ?? z.name,
            onClick: (L) => L.stopPropagation(),
            children: [
              /* @__PURE__ */ a.jsx(
                "button",
                {
                  type: "button",
                  className: "tfdWizard__modalClose",
                  onClick: () => T(null),
                  "aria-label": "Produktansicht schließen",
                  children: "×"
                }
              ),
              /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__productModalMedia", children: D ? /* @__PURE__ */ a.jsx("img", { src: D, alt: (g == null ? void 0 : g.title) ?? z.name }) : /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__productModalPlaceholder", children: "No1" }) }),
              /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__productModalBody", children: [
                /* @__PURE__ */ a.jsx("span", { children: "Produktdetails" }),
                /* @__PURE__ */ a.jsx("h3", { children: (g == null ? void 0 : g.title) ?? z.name }),
                S && /* @__PURE__ */ a.jsxs("p", { children: [
                  F && /* @__PURE__ */ a.jsx("s", { className: "tfdWizard__priceOriginal", children: F }),
                  /* @__PURE__ */ a.jsx("strong", { children: S })
                ] }),
                z.description && /* @__PURE__ */ a.jsx("p", { children: z.description }),
                /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__productFacts", children: [
                  (g == null ? void 0 : g.sku) && /* @__PURE__ */ a.jsxs("div", { children: [
                    /* @__PURE__ */ a.jsx("em", { children: "SKU" }),
                    /* @__PURE__ */ a.jsx("b", { children: g.sku })
                  ] }),
                  z.level && /* @__PURE__ */ a.jsxs("div", { children: [
                    /* @__PURE__ */ a.jsx("em", { children: "Tool-Level" }),
                    /* @__PURE__ */ a.jsx("b", { children: z.level })
                  ] }),
                  /* @__PURE__ */ a.jsxs("div", { children: [
                    /* @__PURE__ */ a.jsx("em", { children: "Empfehlung" }),
                    /* @__PURE__ */ a.jsx("b", { children: z.fromCombination ? "Exakt passend" : "Passend" })
                  ] })
                ] }),
                /* @__PURE__ */ a.jsx(
                  "button",
                  {
                    type: "button",
                    className: "tfdWizard__modalSelect",
                    onClick: () => mu(z.key),
                    "aria-pressed": ce,
                    children: ce ? "Aus Auswahl entfernen" : "Zur Auswahl hinzufügen"
                  }
                )
              ] })
            ]
          }
        )
      }
    );
  }, Gc = async () => {
    if (!O || h) return;
    _(!0), k("");
    const g = js(e, m, On, Wn, b);
    try {
      await Wp(t, g), k("Anfrage wurde gespeichert. Wir melden uns mit der passenden Empfehlung.");
    } catch (S) {
      const F = S instanceof Error ? S.message : "Unbekannter API-Fehler";
      k(`Anfrage konnte nicht gesendet werden: ${F}`);
    } finally {
      _(!1);
    }
  }, Yc = () => {
    var M, Ie;
    const g = (m.vehicles ?? []).map((C) => se.find((B) => B.value === C)).filter((C) => !!C).slice(0, 4), S = (m.services ?? []).map((C) => It.find((B) => B.value === C)).filter((C) => !!C).slice(0, 4), F = (M = Dt.find((C) => C.value === m.tool)) == null ? void 0 : M.title, D = (b.recommendedTools ?? []).filter((C) => (m.selectedTools ?? []).includes(C.key)), ce = D.reduce((C, B) => {
      const ze = Number($t(B.product));
      return Number.isFinite(ze) ? C + ze : C;
    }, 0), L = !!((Ie = m.vehicles) != null && Ie.length && m.tool), Pe = !!(m.tool && m.tool !== "none");
    return /* @__PURE__ */ a.jsxs("aside", { className: "tfdWizard__promoAside", "aria-label": "Tool Empfehlungen", children: [
      (g.length > 0 || S.length > 0 || F) && /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__promoCard tfdWizard__summaryCard", children: [
        /* @__PURE__ */ a.jsx("span", { children: "Deine Angaben" }),
        F && /* @__PURE__ */ a.jsxs("p", { className: "tfdWizard__summaryLine", children: [
          /* @__PURE__ */ a.jsx("b", { children: "Tool:" }),
          /* @__PURE__ */ a.jsx("span", { className: `tfdWizard__statusIcon ${Pe ? "tfdWizard__statusIcon--yes" : "tfdWizard__statusIcon--no"}`, children: Pe ? /* @__PURE__ */ a.jsx(_i, {}) : /* @__PURE__ */ a.jsx(qp, {}) }),
          F
        ] }),
        g.length > 0 && /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__summaryLine", children: [
          /* @__PURE__ */ a.jsx("b", { children: "Fahrzeuge:" }),
          /* @__PURE__ */ a.jsx("span", { className: "tfdWizard__summaryIconList", children: g.map((C) => {
            const B = C.imageUrl ?? C.icon;
            return /* @__PURE__ */ a.jsxs("span", { className: "tfdWizard__summaryIconItem", title: C.title, children: [
              B ? /* @__PURE__ */ a.jsx("img", { src: B, alt: "", loading: "lazy" }) : /* @__PURE__ */ a.jsx("span", { className: "tfdWizard__statusIcon tfdWizard__statusIcon--yes", children: /* @__PURE__ */ a.jsx(_i, {}) }),
              C.title
            ] }, C.value);
          }) })
        ] }),
        S.length > 0 && /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__summaryLine", children: [
          /* @__PURE__ */ a.jsx("b", { children: "Leistungen:" }),
          /* @__PURE__ */ a.jsx("span", { className: "tfdWizard__summaryIconList", children: S.map((C) => {
            const B = C.imageUrl ?? C.icon;
            return /* @__PURE__ */ a.jsxs("span", { className: "tfdWizard__summaryIconItem", title: C.title, children: [
              B ? /* @__PURE__ */ a.jsx("img", { src: B, alt: "", loading: "lazy" }) : /* @__PURE__ */ a.jsx("span", { className: "tfdWizard__statusIcon tfdWizard__statusIcon--yes", children: /* @__PURE__ */ a.jsx(_i, {}) }),
              C.title
            ] }, C.value);
          }) })
        ] })
      ] }),
      !Pe && ae.type !== "contact" && /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__promoCard tfdWizard__promoCard--accent", children: [
        /* @__PURE__ */ a.jsx("span", { children: "Tool Werbung" }),
        /* @__PURE__ */ a.jsx("strong", { children: "Passendes Leseschreibtool" }),
        /* @__PURE__ */ a.jsx("p", { children: "Für den Händlerstart brauchst du ein unterstütztes Tool. Wir prüfen Protokolle und Fahrzeugbereiche." }),
        L && c && /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__miniNotice", children: c }),
        L ? y ? /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__miniNotice", children: "Empfehlung wird aus dem Backend geladen..." }) : b.recommendedTools && b.recommendedTools.length > 0 ? /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__adList", children: b.recommendedTools.slice(0, 4).map((C) => {
          var gt;
          const B = De($t(C.product)), ze = ln(C.product) ? De((gt = C.product) == null ? void 0 : gt.price) : void 0;
          return /* @__PURE__ */ a.jsxs("article", { className: C.imageUrl ? "tfdWizard__adItem--withImage" : void 0, children: [
            C.imageUrl && /* @__PURE__ */ a.jsx("img", { className: "tfdWizard__adImage", src: C.imageUrl, alt: C.name, loading: "lazy" }),
            /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__adBody", children: [
              /* @__PURE__ */ a.jsx("b", { children: C.name }),
              C.fromCombination && /* @__PURE__ */ a.jsx("span", { className: "tfdWizard__adBadge", children: "Passt zu deiner Auswahl" }),
              C.description && /* @__PURE__ */ a.jsx("small", { children: C.description }),
              C.product && /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__adProduct", children: [
                ze && /* @__PURE__ */ a.jsx("s", { className: "tfdWizard__priceOriginal", children: ze }),
                B && /* @__PURE__ */ a.jsx("span", { className: "tfdWizard__adPrice", children: B }),
                /* @__PURE__ */ a.jsx(
                  "button",
                  {
                    type: "button",
                    className: "tfdWizard__adLink",
                    onClick: () => Ql(C),
                    children: "Produkt ansehen"
                  }
                )
              ] })
            ] })
          ] }, C.key);
        }) }) : L ? /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__miniNotice", children: "Für diese Fahrzeugauswahl ist noch keine passende Tool-Empfehlung hinterlegt." }) : /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__tagList", children: [
          /* @__PURE__ */ a.jsx("em", { children: "Autotuner" }),
          /* @__PURE__ */ a.jsx("em", { children: "KESS3" }),
          /* @__PURE__ */ a.jsx("em", { children: "FLEX" })
        ] }) : /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__miniNotice", children: "Beantworte Fahrzeuge und Tool-Status, um passende Tools zu sehen." }),
        b.recommendedTools && b.recommendedTools.length > 0 && /* @__PURE__ */ a.jsx("p", { className: "tfdWizard__priceDisclaimer", children: "* Preise netto, zzgl. gesetzl. MwSt." })
      ] }),
      l.toolDisclaimer && ae.key === "vehicleTool" && /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__miniNotice", children: l.toolDisclaimer }),
      ae.type === "contact" && /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__promoCard tfdWizard__selectedProductsCard", children: [
        /* @__PURE__ */ a.jsx("span", { children: "Ausgewählte Produkte" }),
        D.length > 0 ? /* @__PURE__ */ a.jsxs(a.Fragment, { children: [
          /* @__PURE__ */ a.jsxs("strong", { children: [
            D.length,
            " Position",
            D.length === 1 ? "" : "en"
          ] }),
          /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__selectedProductsList", children: D.map((C) => {
            var gt, ee, hu, vu, gu;
            const B = De($t(C.product)), ze = ln(C.product) ? De((gt = C.product) == null ? void 0 : gt.price) : void 0;
            return /* @__PURE__ */ a.jsxs("article", { children: [
              (C.imageUrl || ((ee = C.product) == null ? void 0 : ee.imageUrl)) && /* @__PURE__ */ a.jsx("img", { src: C.imageUrl ?? ((hu = C.product) == null ? void 0 : hu.imageUrl), alt: ((vu = C.product) == null ? void 0 : vu.title) ?? C.name, loading: "lazy" }),
              /* @__PURE__ */ a.jsxs("div", { children: [
                /* @__PURE__ */ a.jsx("b", { children: ((gu = C.product) == null ? void 0 : gu.title) ?? C.name }),
                ze && /* @__PURE__ */ a.jsx("s", { className: "tfdWizard__priceOriginal", children: ze }),
                B && /* @__PURE__ */ a.jsx("em", { children: B }),
                /* @__PURE__ */ a.jsx("button", { type: "button", onClick: () => Ql(C), children: "Details" })
              ] })
            ] }, C.key);
          }) }),
          ce > 0 && /* @__PURE__ */ a.jsxs("p", { className: "tfdWizard__selectedProductsTotal", children: [
            /* @__PURE__ */ a.jsx("b", { children: "Summe:" }),
            " ",
            De(String(ce))
          ] }),
          /* @__PURE__ */ a.jsx("p", { className: "tfdWizard__priceDisclaimer", children: "* Preise netto, zzgl. gesetzl. MwSt." })
        ] }) : /* @__PURE__ */ a.jsx("p", { children: "Keine Produkte ausgewählt. Du kannst im vorherigen Schritt passende Tools auswählen." })
      ] })
    ] });
  }, Xc = (g) => /* @__PURE__ */ a.jsx(a.Fragment, { children: g.questions.filter((S) => Qr(S, m)).map((S) => /* @__PURE__ */ a.jsxs("section", { className: "tfdWizard__block", children: [
    /* @__PURE__ */ a.jsx("h3", { className: "tfdWizard__title", children: S.title }),
    S.questionType === "vehicleType" && /* @__PURE__ */ a.jsx(
      "button",
      {
        type: "button",
        className: "tfdWizard__selectAllButton",
        onClick: it,
        "data-testid": "vehicles-select-all-button",
        children: Ut ? "Alle abwählen" : "Alle auswählen"
      }
    ),
    S.helpText && /* @__PURE__ */ a.jsx("p", { className: "tfdWizard__lead", children: S.helpText }),
    Rn(R[S.answerKey] ?? [], S.answerKey, S.multiSelect)
  ] }, S.answerKey)) }), Zc = (g) => {
    const S = (L) => L.fieldType === "checkbox" && !L.multiSelect, F = g.questions.filter((L) => Qr(L, m)), D = F.filter((L) => !S(L)), ce = F.filter(S);
    return /* @__PURE__ */ a.jsxs("section", { className: "tfdWizard__block", children: [
      /* @__PURE__ */ a.jsx("h3", { className: "tfdWizard__title", children: "Kontakt aufnehmen" }),
      /* @__PURE__ */ a.jsx("p", { className: "tfdWizard__lead", children: "Sende uns deine Angaben. Wir prüfen dein Partner-Modell und melden uns mit der passenden Empfehlung." }),
      /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__form", children: D.map((L) => {
        const Pe = Zn(m, L.answerKey), M = typeof Pe == "string" ? Pe : "", Ie = Array.isArray(Pe) ? Pe : [], C = `${L.title}${L.required ? " *" : ""}`, B = `contact-${L.answerKey}-input`, ze = L.fieldType === "radio" || L.fieldType === "checkbox", gt = L.fieldType === "textarea" || ze;
        return ze ? /* @__PURE__ */ a.jsxs("div", { className: gt ? "tfdWizard__fieldFull" : void 0, children: [
          /* @__PURE__ */ a.jsx("span", { className: "tfdWizard__checkboxGroupLabel", children: C }),
          L.fieldType === "radio" ? /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__radioGroup", "data-testid": B, children: L.options.map((ee) => /* @__PURE__ */ a.jsxs("label", { className: "tfdWizard__radioOption", children: [
            /* @__PURE__ */ a.jsx(
              "input",
              {
                type: "radio",
                name: L.answerKey,
                checked: M === ee.value,
                onChange: () => jr(L.answerKey, ee.value)
              }
            ),
            /* @__PURE__ */ a.jsx("span", { children: ee.title })
          ] }, ee.value)) }) : /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__checkboxGroup", "data-testid": B, children: L.options.map((ee) => /* @__PURE__ */ a.jsxs("label", { className: "tfdWizard__checkboxOption", children: [
            /* @__PURE__ */ a.jsx(
              "input",
              {
                type: "checkbox",
                checked: Ie.includes(ee.value),
                onChange: () => Hc(L.answerKey, ee.value)
              }
            ),
            /* @__PURE__ */ a.jsx("span", { children: ee.title })
          ] }, ee.value)) })
        ] }, L.answerKey) : /* @__PURE__ */ a.jsxs("label", { className: gt ? "tfdWizard__fieldFull" : void 0, children: [
          /* @__PURE__ */ a.jsx("span", { children: C }),
          L.fieldType === "textarea" ? /* @__PURE__ */ a.jsx(
            "textarea",
            {
              value: M,
              onChange: (ee) => jr(L.answerKey, ee.target.value),
              "data-testid": B
            }
          ) : L.fieldType === "select" ? /* @__PURE__ */ a.jsxs(
            "select",
            {
              value: M,
              onChange: (ee) => jr(L.answerKey, ee.target.value),
              "data-testid": B,
              children: [
                /* @__PURE__ */ a.jsx("option", { value: "", children: "Bitte wählen" }),
                L.options.map((ee) => /* @__PURE__ */ a.jsx("option", { value: ee.value, children: ee.title }, ee.value))
              ]
            }
          ) : /* @__PURE__ */ a.jsx(
            "input",
            {
              type: L.fieldType === "email" ? "email" : "text",
              value: M,
              onChange: (ee) => jr(L.answerKey, ee.target.value),
              "data-testid": B
            }
          )
        ] }, L.answerKey);
      }) }),
      ce.map((L) => {
        const Pe = Zn(m, L.answerKey) === !0 || Zn(m, L.answerKey) === "true";
        return /* @__PURE__ */ a.jsxs("label", { className: "tfdWizard__privacy", children: [
          /* @__PURE__ */ a.jsx(
            "input",
            {
              type: "checkbox",
              checked: Pe,
              onChange: (M) => Kc(L.answerKey, M.target.checked),
              "data-testid": `contact-${L.answerKey}-checkbox`
            }
          ),
          /* @__PURE__ */ a.jsx("span", { children: L.title })
        ] }, L.answerKey);
      }),
      x && /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__notice", children: x })
    ] });
  }, Jc = () => {
    var g, S;
    switch (ae.type) {
      case "questions":
        return Xc(ae);
      case "recommendation": {
        const F = Array.from(new Set([...b.bullets ?? [], ...Yp].filter(Boolean))), D = ((g = b.recommendedTools) == null ? void 0 : g.slice(0, 4)) ?? [], ce = m.selectedTools ?? [], L = D.filter((M) => ce.includes(M.key)), Pe = L.reduce((M, Ie) => {
          const C = Number($t(Ie.product));
          return Number.isFinite(C) ? M + C : M;
        }, 0);
        return /* @__PURE__ */ a.jsxs("section", { className: "tfdWizard__block tfdWizard__recommendationStep", children: [
          /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__recommendationHero", children: [
            /* @__PURE__ */ a.jsx("span", { children: "Deine Empfehlung" }),
            /* @__PURE__ */ a.jsx("h3", { children: b.packageName }),
            /* @__PURE__ */ a.jsx("p", { children: b.summary })
          ] }),
          /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__recommendationPanel", children: [
            /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__recommendationPanelHeader", children: [
              /* @__PURE__ */ a.jsx("span", { children: "Passende Tools" }),
              y && /* @__PURE__ */ a.jsx("em", { children: "Wird geladen..." })
            ] }),
            D.length > 0 ? /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__toolShowcase", children: D.map((M) => {
              var ze;
              const Ie = De($t(M.product)), C = ln(M.product) ? De((ze = M.product) == null ? void 0 : ze.price) : void 0, B = ce.includes(M.key);
              return /* @__PURE__ */ a.jsxs(
                "article",
                {
                  className: [
                    "tfdWizard__toolShowcaseItem",
                    M.imageUrl ? "tfdWizard__toolShowcaseItem--withImage" : "",
                    B ? "tfdWizard__toolShowcaseItem--selected" : ""
                  ].filter(Boolean).join(" "),
                  children: [
                    M.imageUrl && /* @__PURE__ */ a.jsx("img", { className: "tfdWizard__toolShowcaseImage", src: M.imageUrl, alt: M.name, loading: "lazy" }),
                    /* @__PURE__ */ a.jsxs("div", { children: [
                      /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__toolShowcaseTop", children: [
                        /* @__PURE__ */ a.jsx("b", { children: M.name }),
                        /* @__PURE__ */ a.jsx(
                          "button",
                          {
                            type: "button",
                            className: "tfdWizard__cartToggle",
                            onClick: () => mu(M.key),
                            "aria-pressed": B,
                            children: B ? "Ausgewählt" : "Auswählen"
                          }
                        )
                      ] }),
                      M.fromCombination && /* @__PURE__ */ a.jsx("small", { children: "Passt exakt zu deiner Auswahl" }),
                      M.description && /* @__PURE__ */ a.jsx("p", { children: M.description }),
                      /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__toolShowcaseMeta", children: [
                        M.level && /* @__PURE__ */ a.jsx("span", { children: M.level }),
                        C && /* @__PURE__ */ a.jsx("s", { className: "tfdWizard__priceOriginal", children: C }),
                        Ie && /* @__PURE__ */ a.jsx("strong", { children: Ie }),
                        M.product && /* @__PURE__ */ a.jsx("button", { type: "button", onClick: () => Ql(M), children: "Produkt ansehen" })
                      ] })
                    ] })
                  ]
                },
                M.key
              );
            }) }) : /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__miniNotice", children: "Für diese Auswahl ist noch keine passende Tool-Empfehlung hinterlegt." }),
            /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__cartSummary", children: [
              /* @__PURE__ */ a.jsx("span", { children: "Auswahl" }),
              L.length > 0 ? /* @__PURE__ */ a.jsxs(a.Fragment, { children: [
                /* @__PURE__ */ a.jsxs("strong", { children: [
                  L.length,
                  " Position",
                  L.length === 1 ? "" : "en",
                  Pe > 0 ? ` · ${De(String(Pe))}` : ""
                ] }),
                /* @__PURE__ */ a.jsx("p", { children: L.map((M) => M.name).join(", ") })
              ] }) : /* @__PURE__ */ a.jsx("p", { children: "Wähle ein oder mehrere passende Tools aus. Die Auswahl wird mit deiner Anfrage übergeben." })
            ] })
          ] }),
          b.recommendedProducts && b.recommendedProducts.length > 0 && /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__recommendationPanel", children: [
            /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__recommendationPanelHeader", children: /* @__PURE__ */ a.jsx("span", { children: "Produktempfehlungen" }) }),
            /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__toolShowcase", children: b.recommendedProducts.map((M) => {
              const Ie = De($t(M)), C = ln(M) ? De(M.price) : void 0;
              return /* @__PURE__ */ a.jsxs(
                "article",
                {
                  className: [
                    "tfdWizard__toolShowcaseItem",
                    M.imageUrl ? "tfdWizard__toolShowcaseItem--withImage" : ""
                  ].filter(Boolean).join(" "),
                  children: [
                    M.imageUrl && /* @__PURE__ */ a.jsx("img", { className: "tfdWizard__toolShowcaseImage", src: M.imageUrl, alt: M.title, loading: "lazy" }),
                    /* @__PURE__ */ a.jsxs("div", { children: [
                      /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__toolShowcaseTop", children: /* @__PURE__ */ a.jsx("b", { children: M.title }) }),
                      /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__toolShowcaseMeta", children: [
                        M.sku && /* @__PURE__ */ a.jsx("span", { children: M.sku }),
                        C && /* @__PURE__ */ a.jsx("s", { className: "tfdWizard__priceOriginal", children: C }),
                        Ie && /* @__PURE__ */ a.jsx("strong", { children: Ie })
                      ] })
                    ] })
                  ]
                },
                M.uid ?? M.title
              );
            }) })
          ] }),
          (D.length > 0 || (((S = b.recommendedProducts) == null ? void 0 : S.length) ?? 0) > 0) && /* @__PURE__ */ a.jsx("p", { className: "tfdWizard__priceDisclaimer", children: "* Preise netto, zzgl. gesetzl. MwSt." }),
          /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__extrasPanel", children: [
            /* @__PURE__ */ a.jsx("span", { children: "Mit drin" }),
            /* @__PURE__ */ a.jsx("ul", { children: F.map((M) => /* @__PURE__ */ a.jsx("li", { children: M }, M)) })
          ] })
        ] });
      }
      case "contact":
        return Zc(ae);
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
        /* @__PURE__ */ a.jsx("div", { className: "tfdWizard__progress", children: /* @__PURE__ */ a.jsx("div", { style: { width: `${W}%` } }) }),
        /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__layout", children: [
          /* @__PURE__ */ a.jsx("aside", { className: "tfdWizard__steps", "aria-label": "Wizard Schritte", children: V.map((g, S) => {
            const F = [
              S === s ? "active" : "",
              S < s ? "done" : ""
            ].filter(Boolean).join(" ");
            return /* @__PURE__ */ a.jsxs(
              "button",
              {
                type: "button",
                className: F,
                onClick: () => {
                  S <= s && (d(S), k(""));
                },
                "data-testid": `step-${g.key}`,
                children: [
                  /* @__PURE__ */ a.jsx("b", { children: S < s ? /* @__PURE__ */ a.jsx("svg", { viewBox: "0 0 24 24", "aria-hidden": "true", focusable: "false", children: /* @__PURE__ */ a.jsx("path", { d: "M5 12.5l4.2 4.2L19 7" }) }) : S + 1 }),
                  /* @__PURE__ */ a.jsx("span", { children: g.label })
                ]
              },
              g.key
            );
          }) }),
          /* @__PURE__ */ a.jsxs("div", { className: `tfdWizard__body ${ae.type === "recommendation" ? "tfdWizard__body--full" : ""}`, children: [
            /* @__PURE__ */ a.jsx("main", { className: "tfdWizard__mainPane", children: Jc() }),
            ae.type !== "recommendation" && Yc()
          ] })
        ] }),
        /* @__PURE__ */ a.jsxs("div", { className: "tfdWizard__actions", children: [
          /* @__PURE__ */ a.jsx(
            "button",
            {
              type: "button",
              onClick: nn,
              disabled: s === 0,
              "data-testid": "wizard-back-button",
              children: "Zurück"
            }
          ),
          ae.type === "contact" ? /* @__PURE__ */ a.jsx(
            "button",
            {
              type: "button",
              onClick: Gc,
              disabled: !O || h,
              "data-testid": "wizard-submit-button",
              children: h ? "Senden..." : b.cta
            }
          ) : /* @__PURE__ */ a.jsx(
            "button",
            {
              type: "button",
              onClick: ot,
              disabled: !O,
              "data-testid": "wizard-next-button",
              children: "Weiter"
            }
          )
        ] }),
        Qc()
      ]
    }
  );
}
class tm extends HTMLElement {
  constructor() {
    super(...arguments);
    Gl(this, "root");
    Gl(this, "mount");
  }
  connectedCallback() {
    if (this.root)
      return;
    this.mount = document.createElement("div"), this.appendChild(this.mount);
    const n = this.getAttribute("client-id") || "no1", r = this.getAttribute("api-base-url") || "", l = this.getAttribute("primary-color") || "#E31E24", o = (this.getAttribute("theme") || "light") === "dark" ? "dark" : "light";
    this.style.setProperty("--partner-wizard-primary", l), this.root = $c(this.mount), this.root.render(
      /* @__PURE__ */ a.jsx(
        em,
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
customElements.get("tfd-partner-wizard") || customElements.define("tfd-partner-wizard", tm);
export {
  em as PartnerWizard
};
