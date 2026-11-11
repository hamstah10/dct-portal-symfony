var Pf = Object.defineProperty;
var Af = (e, t, n) => t in e ? Pf(e, t, { enumerable: !0, configurable: !0, writable: !0, value: n }) : e[t] = n;
var Ri = (e, t, n) => Af(e, typeof t != "symbol" ? t + "" : t, n);
var Fu = { exports: {} }, ki = {}, Du = { exports: {} }, K = {};
/**
 * @license React
 * react.production.min.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */
var Yr = Symbol.for("react.element"), Lf = Symbol.for("react.portal"), Wf = Symbol.for("react.fragment"), Ff = Symbol.for("react.strict_mode"), Df = Symbol.for("react.profiler"), Of = Symbol.for("react.provider"), If = Symbol.for("react.context"), Mf = Symbol.for("react.forward_ref"), Rf = Symbol.for("react.suspense"), Bf = Symbol.for("react.memo"), $f = Symbol.for("react.lazy"), xa = Symbol.iterator;
function Uf(e) {
  return e === null || typeof e != "object" ? null : (e = xa && e[xa] || e["@@iterator"], typeof e == "function" ? e : null);
}
var Ou = { isMounted: function() {
  return !1;
}, enqueueForceUpdate: function() {
}, enqueueReplaceState: function() {
}, enqueueSetState: function() {
} }, Iu = Object.assign, Mu = {};
function Yn(e, t, n) {
  this.props = e, this.context = t, this.refs = Mu, this.updater = n || Ou;
}
Yn.prototype.isReactComponent = {};
Yn.prototype.setState = function(e, t) {
  if (typeof e != "object" && typeof e != "function" && e != null) throw Error("setState(...): takes an object of state variables to update or a function which returns an object of state variables.");
  this.updater.enqueueSetState(this, e, t, "setState");
};
Yn.prototype.forceUpdate = function(e) {
  this.updater.enqueueForceUpdate(this, e, "forceUpdate");
};
function Ru() {
}
Ru.prototype = Yn.prototype;
function as(e, t, n) {
  this.props = e, this.context = t, this.refs = Mu, this.updater = n || Ou;
}
var us = as.prototype = new Ru();
us.constructor = as;
Iu(us, Yn.prototype);
us.isPureReactComponent = !0;
var wa = Array.isArray, Bu = Object.prototype.hasOwnProperty, cs = { current: null }, $u = { key: !0, ref: !0, __self: !0, __source: !0 };
function Uu(e, t, n) {
  var r, l = {}, i = null, o = null;
  if (t != null) for (r in t.ref !== void 0 && (o = t.ref), t.key !== void 0 && (i = "" + t.key), t) Bu.call(t, r) && !$u.hasOwnProperty(r) && (l[r] = t[r]);
  var a = arguments.length - 2;
  if (a === 1) l.children = n;
  else if (1 < a) {
    for (var u = Array(a), d = 0; d < a; d++) u[d] = arguments[d + 2];
    l.children = u;
  }
  if (e && e.defaultProps) for (r in a = e.defaultProps, a) l[r] === void 0 && (l[r] = a[r]);
  return { $$typeof: Yr, type: e, key: i, ref: o, props: l, _owner: cs.current };
}
function Kf(e, t) {
  return { $$typeof: Yr, type: e.type, key: t, ref: e.ref, props: e.props, _owner: e._owner };
}
function ds(e) {
  return typeof e == "object" && e !== null && e.$$typeof === Yr;
}
function Vf(e) {
  var t = { "=": "=0", ":": "=2" };
  return "$" + e.replace(/[=:]/g, function(n) {
    return t[n];
  });
}
var _a = /\/+/g;
function Bi(e, t) {
  return typeof e == "object" && e !== null && e.key != null ? Vf("" + e.key) : t.toString(36);
}
function Dl(e, t, n, r, l) {
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
        case Yr:
        case Lf:
          o = !0;
      }
  }
  if (o) return o = e, l = l(o), e = r === "" ? "." + Bi(o, 0) : r, wa(l) ? (n = "", e != null && (n = e.replace(_a, "$&/") + "/"), Dl(l, t, n, "", function(d) {
    return d;
  })) : l != null && (ds(l) && (l = Kf(l, n + (!l.key || o && o.key === l.key ? "" : ("" + l.key).replace(_a, "$&/") + "/") + e)), t.push(l)), 1;
  if (o = 0, r = r === "" ? "." : r + ":", wa(e)) for (var a = 0; a < e.length; a++) {
    i = e[a];
    var u = r + Bi(i, a);
    o += Dl(i, t, n, u, l);
  }
  else if (u = Uf(e), typeof u == "function") for (e = u.call(e), a = 0; !(i = e.next()).done; ) i = i.value, u = r + Bi(i, a++), o += Dl(i, t, n, u, l);
  else if (i === "object") throw t = String(e), Error("Objects are not valid as a React child (found: " + (t === "[object Object]" ? "object with keys {" + Object.keys(e).join(", ") + "}" : t) + "). If you meant to render a collection of children, use an array instead.");
  return o;
}
function hl(e, t, n) {
  if (e == null) return e;
  var r = [], l = 0;
  return Dl(e, r, "", "", function(i) {
    return t.call(n, i, l++);
  }), r;
}
function Hf(e) {
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
var Ne = { current: null }, Ol = { transition: null }, Qf = { ReactCurrentDispatcher: Ne, ReactCurrentBatchConfig: Ol, ReactCurrentOwner: cs };
function Ku() {
  throw Error("act(...) is not supported in production builds of React.");
}
K.Children = { map: hl, forEach: function(e, t, n) {
  hl(e, function() {
    t.apply(this, arguments);
  }, n);
}, count: function(e) {
  var t = 0;
  return hl(e, function() {
    t++;
  }), t;
}, toArray: function(e) {
  return hl(e, function(t) {
    return t;
  }) || [];
}, only: function(e) {
  if (!ds(e)) throw Error("React.Children.only expected to receive a single React element child.");
  return e;
} };
K.Component = Yn;
K.Fragment = Wf;
K.Profiler = Df;
K.PureComponent = as;
K.StrictMode = Ff;
K.Suspense = Rf;
K.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED = Qf;
K.act = Ku;
K.cloneElement = function(e, t, n) {
  if (e == null) throw Error("React.cloneElement(...): The argument must be a React element, but you passed " + e + ".");
  var r = Iu({}, e.props), l = e.key, i = e.ref, o = e._owner;
  if (t != null) {
    if (t.ref !== void 0 && (i = t.ref, o = cs.current), t.key !== void 0 && (l = "" + t.key), e.type && e.type.defaultProps) var a = e.type.defaultProps;
    for (u in t) Bu.call(t, u) && !$u.hasOwnProperty(u) && (r[u] = t[u] === void 0 && a !== void 0 ? a[u] : t[u]);
  }
  var u = arguments.length - 2;
  if (u === 1) r.children = n;
  else if (1 < u) {
    a = Array(u);
    for (var d = 0; d < u; d++) a[d] = arguments[d + 2];
    r.children = a;
  }
  return { $$typeof: Yr, type: e.type, key: l, ref: i, props: r, _owner: o };
};
K.createContext = function(e) {
  return e = { $$typeof: If, _currentValue: e, _currentValue2: e, _threadCount: 0, Provider: null, Consumer: null, _defaultValue: null, _globalName: null }, e.Provider = { $$typeof: Of, _context: e }, e.Consumer = e;
};
K.createElement = Uu;
K.createFactory = function(e) {
  var t = Uu.bind(null, e);
  return t.type = e, t;
};
K.createRef = function() {
  return { current: null };
};
K.forwardRef = function(e) {
  return { $$typeof: Mf, render: e };
};
K.isValidElement = ds;
K.lazy = function(e) {
  return { $$typeof: $f, _payload: { _status: -1, _result: e }, _init: Hf };
};
K.memo = function(e, t) {
  return { $$typeof: Bf, type: e, compare: t === void 0 ? null : t };
};
K.startTransition = function(e) {
  var t = Ol.transition;
  Ol.transition = {};
  try {
    e();
  } finally {
    Ol.transition = t;
  }
};
K.unstable_act = Ku;
K.useCallback = function(e, t) {
  return Ne.current.useCallback(e, t);
};
K.useContext = function(e) {
  return Ne.current.useContext(e);
};
K.useDebugValue = function() {
};
K.useDeferredValue = function(e) {
  return Ne.current.useDeferredValue(e);
};
K.useEffect = function(e, t) {
  return Ne.current.useEffect(e, t);
};
K.useId = function() {
  return Ne.current.useId();
};
K.useImperativeHandle = function(e, t, n) {
  return Ne.current.useImperativeHandle(e, t, n);
};
K.useInsertionEffect = function(e, t) {
  return Ne.current.useInsertionEffect(e, t);
};
K.useLayoutEffect = function(e, t) {
  return Ne.current.useLayoutEffect(e, t);
};
K.useMemo = function(e, t) {
  return Ne.current.useMemo(e, t);
};
K.useReducer = function(e, t, n) {
  return Ne.current.useReducer(e, t, n);
};
K.useRef = function(e) {
  return Ne.current.useRef(e);
};
K.useState = function(e) {
  return Ne.current.useState(e);
};
K.useSyncExternalStore = function(e, t, n) {
  return Ne.current.useSyncExternalStore(e, t, n);
};
K.useTransition = function() {
  return Ne.current.useTransition();
};
K.version = "18.3.1";
Du.exports = K;
var P = Du.exports;
/**
 * @license React
 * react-jsx-runtime.production.min.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */
var bf = P, Gf = Symbol.for("react.element"), Xf = Symbol.for("react.fragment"), Yf = Object.prototype.hasOwnProperty, Zf = bf.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED.ReactCurrentOwner, Jf = { key: !0, ref: !0, __self: !0, __source: !0 };
function Vu(e, t, n) {
  var r, l = {}, i = null, o = null;
  n !== void 0 && (i = "" + n), t.key !== void 0 && (i = "" + t.key), t.ref !== void 0 && (o = t.ref);
  for (r in t) Yf.call(t, r) && !Jf.hasOwnProperty(r) && (l[r] = t[r]);
  if (e && e.defaultProps) for (r in t = e.defaultProps, t) l[r] === void 0 && (l[r] = t[r]);
  return { $$typeof: Gf, type: e, key: i, ref: o, props: l, _owner: Zf.current };
}
ki.Fragment = Xf;
ki.jsx = Vu;
ki.jsxs = Vu;
Fu.exports = ki;
var s = Fu.exports, Hu = { exports: {} }, $e = {}, Qu = { exports: {} }, bu = {};
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
  function t(N, M) {
    var B = N.length;
    N.push(M);
    e: for (; 0 < B; ) {
      var b = B - 1 >>> 1, ee = N[b];
      if (0 < l(ee, M)) N[b] = M, N[B] = ee, B = b;
      else break e;
    }
  }
  function n(N) {
    return N.length === 0 ? null : N[0];
  }
  function r(N) {
    if (N.length === 0) return null;
    var M = N[0], B = N.pop();
    if (B !== M) {
      N[0] = B;
      e: for (var b = 0, ee = N.length, Jt = ee >>> 1; b < Jt; ) {
        var dt = 2 * (b + 1) - 1, tr = N[dt], it = dt + 1, Ke = N[it];
        if (0 > l(tr, B)) it < ee && 0 > l(Ke, tr) ? (N[b] = Ke, N[it] = B, b = it) : (N[b] = tr, N[dt] = B, b = dt);
        else if (it < ee && 0 > l(Ke, B)) N[b] = Ke, N[it] = B, b = it;
        else break e;
      }
    }
    return M;
  }
  function l(N, M) {
    var B = N.sortIndex - M.sortIndex;
    return B !== 0 ? B : N.id - M.id;
  }
  if (typeof performance == "object" && typeof performance.now == "function") {
    var i = performance;
    e.unstable_now = function() {
      return i.now();
    };
  } else {
    var o = Date, a = o.now();
    e.unstable_now = function() {
      return o.now() - a;
    };
  }
  var u = [], d = [], y = 1, g = null, h = 3, k = !1, E = !1, z = !1, Q = typeof setTimeout == "function" ? setTimeout : null, p = typeof clearTimeout == "function" ? clearTimeout : null, c = typeof setImmediate < "u" ? setImmediate : null;
  typeof navigator < "u" && navigator.scheduling !== void 0 && navigator.scheduling.isInputPending !== void 0 && navigator.scheduling.isInputPending.bind(navigator.scheduling);
  function m(N) {
    for (var M = n(d); M !== null; ) {
      if (M.callback === null) r(d);
      else if (M.startTime <= N) r(d), M.sortIndex = M.expirationTime, t(u, M);
      else break;
      M = n(d);
    }
  }
  function _(N) {
    if (z = !1, m(N), !E) if (n(u) !== null) E = !0, gn(j);
    else {
      var M = n(d);
      M !== null && er(_, M.startTime - N);
    }
  }
  function j(N, M) {
    E = !1, z && (z = !1, p(O), O = -1), k = !0;
    var B = h;
    try {
      for (m(M), g = n(u); g !== null && (!(g.expirationTime > M) || N && !ve()); ) {
        var b = g.callback;
        if (typeof b == "function") {
          g.callback = null, h = g.priorityLevel;
          var ee = b(g.expirationTime <= M);
          M = e.unstable_now(), typeof ee == "function" ? g.callback = ee : g === n(u) && r(u), m(M);
        } else r(u);
        g = n(u);
      }
      if (g !== null) var Jt = !0;
      else {
        var dt = n(d);
        dt !== null && er(_, dt.startTime - M), Jt = !1;
      }
      return Jt;
    } finally {
      g = null, h = B, k = !1;
    }
  }
  var A = !1, W = null, O = -1, U = 5, $ = -1;
  function ve() {
    return !(e.unstable_now() - $ < U);
  }
  function jt() {
    if (W !== null) {
      var N = e.unstable_now();
      $ = N;
      var M = !0;
      try {
        M = W(!0, N);
      } finally {
        M ? Zt() : (A = !1, W = null);
      }
    } else A = !1;
  }
  var Zt;
  if (typeof c == "function") Zt = function() {
    c(jt);
  };
  else if (typeof MessageChannel < "u") {
    var qn = new MessageChannel(), Oi = qn.port2;
    qn.port1.onmessage = jt, Zt = function() {
      Oi.postMessage(null);
    };
  } else Zt = function() {
    Q(jt, 0);
  };
  function gn(N) {
    W = N, A || (A = !0, Zt());
  }
  function er(N, M) {
    O = Q(function() {
      N(e.unstable_now());
    }, M);
  }
  e.unstable_IdlePriority = 5, e.unstable_ImmediatePriority = 1, e.unstable_LowPriority = 4, e.unstable_NormalPriority = 3, e.unstable_Profiling = null, e.unstable_UserBlockingPriority = 2, e.unstable_cancelCallback = function(N) {
    N.callback = null;
  }, e.unstable_continueExecution = function() {
    E || k || (E = !0, gn(j));
  }, e.unstable_forceFrameRate = function(N) {
    0 > N || 125 < N ? console.error("forceFrameRate takes a positive int between 0 and 125, forcing frame rates higher than 125 fps is not supported") : U = 0 < N ? Math.floor(1e3 / N) : 5;
  }, e.unstable_getCurrentPriorityLevel = function() {
    return h;
  }, e.unstable_getFirstCallbackNode = function() {
    return n(u);
  }, e.unstable_next = function(N) {
    switch (h) {
      case 1:
      case 2:
      case 3:
        var M = 3;
        break;
      default:
        M = h;
    }
    var B = h;
    h = M;
    try {
      return N();
    } finally {
      h = B;
    }
  }, e.unstable_pauseExecution = function() {
  }, e.unstable_requestPaint = function() {
  }, e.unstable_runWithPriority = function(N, M) {
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
    var B = h;
    h = N;
    try {
      return M();
    } finally {
      h = B;
    }
  }, e.unstable_scheduleCallback = function(N, M, B) {
    var b = e.unstable_now();
    switch (typeof B == "object" && B !== null ? (B = B.delay, B = typeof B == "number" && 0 < B ? b + B : b) : B = b, N) {
      case 1:
        var ee = -1;
        break;
      case 2:
        ee = 250;
        break;
      case 5:
        ee = 1073741823;
        break;
      case 4:
        ee = 1e4;
        break;
      default:
        ee = 5e3;
    }
    return ee = B + ee, N = { id: y++, callback: M, priorityLevel: N, startTime: B, expirationTime: ee, sortIndex: -1 }, B > b ? (N.sortIndex = B, t(d, N), n(u) === null && N === n(d) && (z ? (p(O), O = -1) : z = !0, er(_, B - b))) : (N.sortIndex = ee, t(u, N), E || k || (E = !0, gn(j))), N;
  }, e.unstable_shouldYield = ve, e.unstable_wrapCallback = function(N) {
    var M = h;
    return function() {
      var B = h;
      h = M;
      try {
        return N.apply(this, arguments);
      } finally {
        h = B;
      }
    };
  };
})(bu);
Qu.exports = bu;
var qf = Qu.exports;
/**
 * @license React
 * react-dom.production.min.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */
var ep = P, Be = qf;
function S(e) {
  for (var t = "https://reactjs.org/docs/error-decoder.html?invariant=" + e, n = 1; n < arguments.length; n++) t += "&args[]=" + encodeURIComponent(arguments[n]);
  return "Minified React error #" + e + "; visit " + t + " for the full message or use the non-minified dev environment for full errors and additional helpful warnings.";
}
var Gu = /* @__PURE__ */ new Set(), Lr = {};
function mn(e, t) {
  $n(e, t), $n(e + "Capture", t);
}
function $n(e, t) {
  for (Lr[e] = t, e = 0; e < t.length; e++) Gu.add(t[e]);
}
var kt = !(typeof window > "u" || typeof window.document > "u" || typeof window.document.createElement > "u"), go = Object.prototype.hasOwnProperty, tp = /^[:A-Z_a-z\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u02FF\u0370-\u037D\u037F-\u1FFF\u200C-\u200D\u2070-\u218F\u2C00-\u2FEF\u3001-\uD7FF\uF900-\uFDCF\uFDF0-\uFFFD][:A-Z_a-z\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u02FF\u0370-\u037D\u037F-\u1FFF\u200C-\u200D\u2070-\u218F\u2C00-\u2FEF\u3001-\uD7FF\uF900-\uFDCF\uFDF0-\uFFFD\-.0-9\u00B7\u0300-\u036F\u203F-\u2040]*$/, Sa = {}, ja = {};
function np(e) {
  return go.call(ja, e) ? !0 : go.call(Sa, e) ? !1 : tp.test(e) ? ja[e] = !0 : (Sa[e] = !0, !1);
}
function rp(e, t, n, r) {
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
function lp(e, t, n, r) {
  if (t === null || typeof t > "u" || rp(e, t, n, r)) return !0;
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
function Ce(e, t, n, r, l, i, o) {
  this.acceptsBooleans = t === 2 || t === 3 || t === 4, this.attributeName = r, this.attributeNamespace = l, this.mustUseProperty = n, this.propertyName = e, this.type = t, this.sanitizeURL = i, this.removeEmptyString = o;
}
var xe = {};
"children dangerouslySetInnerHTML defaultValue defaultChecked innerHTML suppressContentEditableWarning suppressHydrationWarning style".split(" ").forEach(function(e) {
  xe[e] = new Ce(e, 0, !1, e, null, !1, !1);
});
[["acceptCharset", "accept-charset"], ["className", "class"], ["htmlFor", "for"], ["httpEquiv", "http-equiv"]].forEach(function(e) {
  var t = e[0];
  xe[t] = new Ce(t, 1, !1, e[1], null, !1, !1);
});
["contentEditable", "draggable", "spellCheck", "value"].forEach(function(e) {
  xe[e] = new Ce(e, 2, !1, e.toLowerCase(), null, !1, !1);
});
["autoReverse", "externalResourcesRequired", "focusable", "preserveAlpha"].forEach(function(e) {
  xe[e] = new Ce(e, 2, !1, e, null, !1, !1);
});
"allowFullScreen async autoFocus autoPlay controls default defer disabled disablePictureInPicture disableRemotePlayback formNoValidate hidden loop noModule noValidate open playsInline readOnly required reversed scoped seamless itemScope".split(" ").forEach(function(e) {
  xe[e] = new Ce(e, 3, !1, e.toLowerCase(), null, !1, !1);
});
["checked", "multiple", "muted", "selected"].forEach(function(e) {
  xe[e] = new Ce(e, 3, !0, e, null, !1, !1);
});
["capture", "download"].forEach(function(e) {
  xe[e] = new Ce(e, 4, !1, e, null, !1, !1);
});
["cols", "rows", "size", "span"].forEach(function(e) {
  xe[e] = new Ce(e, 6, !1, e, null, !1, !1);
});
["rowSpan", "start"].forEach(function(e) {
  xe[e] = new Ce(e, 5, !1, e.toLowerCase(), null, !1, !1);
});
var fs = /[\-:]([a-z])/g;
function ps(e) {
  return e[1].toUpperCase();
}
"accent-height alignment-baseline arabic-form baseline-shift cap-height clip-path clip-rule color-interpolation color-interpolation-filters color-profile color-rendering dominant-baseline enable-background fill-opacity fill-rule flood-color flood-opacity font-family font-size font-size-adjust font-stretch font-style font-variant font-weight glyph-name glyph-orientation-horizontal glyph-orientation-vertical horiz-adv-x horiz-origin-x image-rendering letter-spacing lighting-color marker-end marker-mid marker-start overline-position overline-thickness paint-order panose-1 pointer-events rendering-intent shape-rendering stop-color stop-opacity strikethrough-position strikethrough-thickness stroke-dasharray stroke-dashoffset stroke-linecap stroke-linejoin stroke-miterlimit stroke-opacity stroke-width text-anchor text-decoration text-rendering underline-position underline-thickness unicode-bidi unicode-range units-per-em v-alphabetic v-hanging v-ideographic v-mathematical vector-effect vert-adv-y vert-origin-x vert-origin-y word-spacing writing-mode xmlns:xlink x-height".split(" ").forEach(function(e) {
  var t = e.replace(
    fs,
    ps
  );
  xe[t] = new Ce(t, 1, !1, e, null, !1, !1);
});
"xlink:actuate xlink:arcrole xlink:role xlink:show xlink:title xlink:type".split(" ").forEach(function(e) {
  var t = e.replace(fs, ps);
  xe[t] = new Ce(t, 1, !1, e, "http://www.w3.org/1999/xlink", !1, !1);
});
["xml:base", "xml:lang", "xml:space"].forEach(function(e) {
  var t = e.replace(fs, ps);
  xe[t] = new Ce(t, 1, !1, e, "http://www.w3.org/XML/1998/namespace", !1, !1);
});
["tabIndex", "crossOrigin"].forEach(function(e) {
  xe[e] = new Ce(e, 1, !1, e.toLowerCase(), null, !1, !1);
});
xe.xlinkHref = new Ce("xlinkHref", 1, !1, "xlink:href", "http://www.w3.org/1999/xlink", !0, !1);
["src", "href", "action", "formAction"].forEach(function(e) {
  xe[e] = new Ce(e, 1, !1, e.toLowerCase(), null, !0, !0);
});
function ms(e, t, n, r) {
  var l = xe.hasOwnProperty(t) ? xe[t] : null;
  (l !== null ? l.type !== 0 : r || !(2 < t.length) || t[0] !== "o" && t[0] !== "O" || t[1] !== "n" && t[1] !== "N") && (lp(t, n, l, r) && (n = null), r || l === null ? np(t) && (n === null ? e.removeAttribute(t) : e.setAttribute(t, "" + n)) : l.mustUseProperty ? e[l.propertyName] = n === null ? l.type === 3 ? !1 : "" : n : (t = l.attributeName, r = l.attributeNamespace, n === null ? e.removeAttribute(t) : (l = l.type, n = l === 3 || l === 4 && n === !0 ? "" : "" + n, r ? e.setAttributeNS(r, t, n) : e.setAttribute(t, n))));
}
var St = ep.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED, gl = Symbol.for("react.element"), Sn = Symbol.for("react.portal"), jn = Symbol.for("react.fragment"), hs = Symbol.for("react.strict_mode"), vo = Symbol.for("react.profiler"), Xu = Symbol.for("react.provider"), Yu = Symbol.for("react.context"), gs = Symbol.for("react.forward_ref"), yo = Symbol.for("react.suspense"), ko = Symbol.for("react.suspense_list"), vs = Symbol.for("react.memo"), Lt = Symbol.for("react.lazy"), Zu = Symbol.for("react.offscreen"), Ea = Symbol.iterator;
function sr(e) {
  return e === null || typeof e != "object" ? null : (e = Ea && e[Ea] || e["@@iterator"], typeof e == "function" ? e : null);
}
var ie = Object.assign, $i;
function vr(e) {
  if ($i === void 0) try {
    throw Error();
  } catch (n) {
    var t = n.stack.trim().match(/\n( *(at )?)/);
    $i = t && t[1] || "";
  }
  return `
` + $i + e;
}
var Ui = !1;
function Ki(e, t) {
  if (!e || Ui) return "";
  Ui = !0;
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
`), o = l.length - 1, a = i.length - 1; 1 <= o && 0 <= a && l[o] !== i[a]; ) a--;
      for (; 1 <= o && 0 <= a; o--, a--) if (l[o] !== i[a]) {
        if (o !== 1 || a !== 1)
          do
            if (o--, a--, 0 > a || l[o] !== i[a]) {
              var u = `
` + l[o].replace(" at new ", " at ");
              return e.displayName && u.includes("<anonymous>") && (u = u.replace("<anonymous>", e.displayName)), u;
            }
          while (1 <= o && 0 <= a);
        break;
      }
    }
  } finally {
    Ui = !1, Error.prepareStackTrace = n;
  }
  return (e = e ? e.displayName || e.name : "") ? vr(e) : "";
}
function ip(e) {
  switch (e.tag) {
    case 5:
      return vr(e.type);
    case 16:
      return vr("Lazy");
    case 13:
      return vr("Suspense");
    case 19:
      return vr("SuspenseList");
    case 0:
    case 2:
    case 15:
      return e = Ki(e.type, !1), e;
    case 11:
      return e = Ki(e.type.render, !1), e;
    case 1:
      return e = Ki(e.type, !0), e;
    default:
      return "";
  }
}
function xo(e) {
  if (e == null) return null;
  if (typeof e == "function") return e.displayName || e.name || null;
  if (typeof e == "string") return e;
  switch (e) {
    case jn:
      return "Fragment";
    case Sn:
      return "Portal";
    case vo:
      return "Profiler";
    case hs:
      return "StrictMode";
    case yo:
      return "Suspense";
    case ko:
      return "SuspenseList";
  }
  if (typeof e == "object") switch (e.$$typeof) {
    case Yu:
      return (e.displayName || "Context") + ".Consumer";
    case Xu:
      return (e._context.displayName || "Context") + ".Provider";
    case gs:
      var t = e.render;
      return e = e.displayName, e || (e = t.displayName || t.name || "", e = e !== "" ? "ForwardRef(" + e + ")" : "ForwardRef"), e;
    case vs:
      return t = e.displayName || null, t !== null ? t : xo(e.type) || "Memo";
    case Lt:
      t = e._payload, e = e._init;
      try {
        return xo(e(t));
      } catch {
      }
  }
  return null;
}
function op(e) {
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
      return xo(t);
    case 8:
      return t === hs ? "StrictMode" : "Mode";
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
function Qt(e) {
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
function Ju(e) {
  var t = e.type;
  return (e = e.nodeName) && e.toLowerCase() === "input" && (t === "checkbox" || t === "radio");
}
function sp(e) {
  var t = Ju(e) ? "checked" : "value", n = Object.getOwnPropertyDescriptor(e.constructor.prototype, t), r = "" + e[t];
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
function vl(e) {
  e._valueTracker || (e._valueTracker = sp(e));
}
function qu(e) {
  if (!e) return !1;
  var t = e._valueTracker;
  if (!t) return !0;
  var n = t.getValue(), r = "";
  return e && (r = Ju(e) ? e.checked ? "true" : "false" : e.value), e = r, e !== n ? (t.setValue(e), !0) : !1;
}
function Xl(e) {
  if (e = e || (typeof document < "u" ? document : void 0), typeof e > "u") return null;
  try {
    return e.activeElement || e.body;
  } catch {
    return e.body;
  }
}
function wo(e, t) {
  var n = t.checked;
  return ie({}, t, { defaultChecked: void 0, defaultValue: void 0, value: void 0, checked: n ?? e._wrapperState.initialChecked });
}
function Ta(e, t) {
  var n = t.defaultValue == null ? "" : t.defaultValue, r = t.checked != null ? t.checked : t.defaultChecked;
  n = Qt(t.value != null ? t.value : n), e._wrapperState = { initialChecked: r, initialValue: n, controlled: t.type === "checkbox" || t.type === "radio" ? t.checked != null : t.value != null };
}
function ec(e, t) {
  t = t.checked, t != null && ms(e, "checked", t, !1);
}
function _o(e, t) {
  ec(e, t);
  var n = Qt(t.value), r = t.type;
  if (n != null) r === "number" ? (n === 0 && e.value === "" || e.value != n) && (e.value = "" + n) : e.value !== "" + n && (e.value = "" + n);
  else if (r === "submit" || r === "reset") {
    e.removeAttribute("value");
    return;
  }
  t.hasOwnProperty("value") ? So(e, t.type, n) : t.hasOwnProperty("defaultValue") && So(e, t.type, Qt(t.defaultValue)), t.checked == null && t.defaultChecked != null && (e.defaultChecked = !!t.defaultChecked);
}
function za(e, t, n) {
  if (t.hasOwnProperty("value") || t.hasOwnProperty("defaultValue")) {
    var r = t.type;
    if (!(r !== "submit" && r !== "reset" || t.value !== void 0 && t.value !== null)) return;
    t = "" + e._wrapperState.initialValue, n || t === e.value || (e.value = t), e.defaultValue = t;
  }
  n = e.name, n !== "" && (e.name = ""), e.defaultChecked = !!e._wrapperState.initialChecked, n !== "" && (e.name = n);
}
function So(e, t, n) {
  (t !== "number" || Xl(e.ownerDocument) !== e) && (n == null ? e.defaultValue = "" + e._wrapperState.initialValue : e.defaultValue !== "" + n && (e.defaultValue = "" + n));
}
var yr = Array.isArray;
function Dn(e, t, n, r) {
  if (e = e.options, t) {
    t = {};
    for (var l = 0; l < n.length; l++) t["$" + n[l]] = !0;
    for (n = 0; n < e.length; n++) l = t.hasOwnProperty("$" + e[n].value), e[n].selected !== l && (e[n].selected = l), l && r && (e[n].defaultSelected = !0);
  } else {
    for (n = "" + Qt(n), t = null, l = 0; l < e.length; l++) {
      if (e[l].value === n) {
        e[l].selected = !0, r && (e[l].defaultSelected = !0);
        return;
      }
      t !== null || e[l].disabled || (t = e[l]);
    }
    t !== null && (t.selected = !0);
  }
}
function jo(e, t) {
  if (t.dangerouslySetInnerHTML != null) throw Error(S(91));
  return ie({}, t, { value: void 0, defaultValue: void 0, children: "" + e._wrapperState.initialValue });
}
function Na(e, t) {
  var n = t.value;
  if (n == null) {
    if (n = t.children, t = t.defaultValue, n != null) {
      if (t != null) throw Error(S(92));
      if (yr(n)) {
        if (1 < n.length) throw Error(S(93));
        n = n[0];
      }
      t = n;
    }
    t == null && (t = ""), n = t;
  }
  e._wrapperState = { initialValue: Qt(n) };
}
function tc(e, t) {
  var n = Qt(t.value), r = Qt(t.defaultValue);
  n != null && (n = "" + n, n !== e.value && (e.value = n), t.defaultValue == null && e.defaultValue !== n && (e.defaultValue = n)), r != null && (e.defaultValue = "" + r);
}
function Ca(e) {
  var t = e.textContent;
  t === e._wrapperState.initialValue && t !== "" && t !== null && (e.value = t);
}
function nc(e) {
  switch (e) {
    case "svg":
      return "http://www.w3.org/2000/svg";
    case "math":
      return "http://www.w3.org/1998/Math/MathML";
    default:
      return "http://www.w3.org/1999/xhtml";
  }
}
function Eo(e, t) {
  return e == null || e === "http://www.w3.org/1999/xhtml" ? nc(t) : e === "http://www.w3.org/2000/svg" && t === "foreignObject" ? "http://www.w3.org/1999/xhtml" : e;
}
var yl, rc = function(e) {
  return typeof MSApp < "u" && MSApp.execUnsafeLocalFunction ? function(t, n, r, l) {
    MSApp.execUnsafeLocalFunction(function() {
      return e(t, n, r, l);
    });
  } : e;
}(function(e, t) {
  if (e.namespaceURI !== "http://www.w3.org/2000/svg" || "innerHTML" in e) e.innerHTML = t;
  else {
    for (yl = yl || document.createElement("div"), yl.innerHTML = "<svg>" + t.valueOf().toString() + "</svg>", t = yl.firstChild; e.firstChild; ) e.removeChild(e.firstChild);
    for (; t.firstChild; ) e.appendChild(t.firstChild);
  }
});
function Wr(e, t) {
  if (t) {
    var n = e.firstChild;
    if (n && n === e.lastChild && n.nodeType === 3) {
      n.nodeValue = t;
      return;
    }
  }
  e.textContent = t;
}
var _r = {
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
}, ap = ["Webkit", "ms", "Moz", "O"];
Object.keys(_r).forEach(function(e) {
  ap.forEach(function(t) {
    t = t + e.charAt(0).toUpperCase() + e.substring(1), _r[t] = _r[e];
  });
});
function lc(e, t, n) {
  return t == null || typeof t == "boolean" || t === "" ? "" : n || typeof t != "number" || t === 0 || _r.hasOwnProperty(e) && _r[e] ? ("" + t).trim() : t + "px";
}
function ic(e, t) {
  e = e.style;
  for (var n in t) if (t.hasOwnProperty(n)) {
    var r = n.indexOf("--") === 0, l = lc(n, t[n], r);
    n === "float" && (n = "cssFloat"), r ? e.setProperty(n, l) : e[n] = l;
  }
}
var up = ie({ menuitem: !0 }, { area: !0, base: !0, br: !0, col: !0, embed: !0, hr: !0, img: !0, input: !0, keygen: !0, link: !0, meta: !0, param: !0, source: !0, track: !0, wbr: !0 });
function To(e, t) {
  if (t) {
    if (up[e] && (t.children != null || t.dangerouslySetInnerHTML != null)) throw Error(S(137, e));
    if (t.dangerouslySetInnerHTML != null) {
      if (t.children != null) throw Error(S(60));
      if (typeof t.dangerouslySetInnerHTML != "object" || !("__html" in t.dangerouslySetInnerHTML)) throw Error(S(61));
    }
    if (t.style != null && typeof t.style != "object") throw Error(S(62));
  }
}
function zo(e, t) {
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
var No = null;
function ys(e) {
  return e = e.target || e.srcElement || window, e.correspondingUseElement && (e = e.correspondingUseElement), e.nodeType === 3 ? e.parentNode : e;
}
var Co = null, On = null, In = null;
function Pa(e) {
  if (e = qr(e)) {
    if (typeof Co != "function") throw Error(S(280));
    var t = e.stateNode;
    t && (t = ji(t), Co(e.stateNode, e.type, t));
  }
}
function oc(e) {
  On ? In ? In.push(e) : In = [e] : On = e;
}
function sc() {
  if (On) {
    var e = On, t = In;
    if (In = On = null, Pa(e), t) for (e = 0; e < t.length; e++) Pa(t[e]);
  }
}
function ac(e, t) {
  return e(t);
}
function uc() {
}
var Vi = !1;
function cc(e, t, n) {
  if (Vi) return e(t, n);
  Vi = !0;
  try {
    return ac(e, t, n);
  } finally {
    Vi = !1, (On !== null || In !== null) && (uc(), sc());
  }
}
function Fr(e, t) {
  var n = e.stateNode;
  if (n === null) return null;
  var r = ji(n);
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
  if (n && typeof n != "function") throw Error(S(231, t, typeof n));
  return n;
}
var Po = !1;
if (kt) try {
  var ar = {};
  Object.defineProperty(ar, "passive", { get: function() {
    Po = !0;
  } }), window.addEventListener("test", ar, ar), window.removeEventListener("test", ar, ar);
} catch {
  Po = !1;
}
function cp(e, t, n, r, l, i, o, a, u) {
  var d = Array.prototype.slice.call(arguments, 3);
  try {
    t.apply(n, d);
  } catch (y) {
    this.onError(y);
  }
}
var Sr = !1, Yl = null, Zl = !1, Ao = null, dp = { onError: function(e) {
  Sr = !0, Yl = e;
} };
function fp(e, t, n, r, l, i, o, a, u) {
  Sr = !1, Yl = null, cp.apply(dp, arguments);
}
function pp(e, t, n, r, l, i, o, a, u) {
  if (fp.apply(this, arguments), Sr) {
    if (Sr) {
      var d = Yl;
      Sr = !1, Yl = null;
    } else throw Error(S(198));
    Zl || (Zl = !0, Ao = d);
  }
}
function hn(e) {
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
function dc(e) {
  if (e.tag === 13) {
    var t = e.memoizedState;
    if (t === null && (e = e.alternate, e !== null && (t = e.memoizedState)), t !== null) return t.dehydrated;
  }
  return null;
}
function Aa(e) {
  if (hn(e) !== e) throw Error(S(188));
}
function mp(e) {
  var t = e.alternate;
  if (!t) {
    if (t = hn(e), t === null) throw Error(S(188));
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
        if (i === n) return Aa(l), e;
        if (i === r) return Aa(l), t;
        i = i.sibling;
      }
      throw Error(S(188));
    }
    if (n.return !== r.return) n = l, r = i;
    else {
      for (var o = !1, a = l.child; a; ) {
        if (a === n) {
          o = !0, n = l, r = i;
          break;
        }
        if (a === r) {
          o = !0, r = l, n = i;
          break;
        }
        a = a.sibling;
      }
      if (!o) {
        for (a = i.child; a; ) {
          if (a === n) {
            o = !0, n = i, r = l;
            break;
          }
          if (a === r) {
            o = !0, r = i, n = l;
            break;
          }
          a = a.sibling;
        }
        if (!o) throw Error(S(189));
      }
    }
    if (n.alternate !== r) throw Error(S(190));
  }
  if (n.tag !== 3) throw Error(S(188));
  return n.stateNode.current === n ? e : t;
}
function fc(e) {
  return e = mp(e), e !== null ? pc(e) : null;
}
function pc(e) {
  if (e.tag === 5 || e.tag === 6) return e;
  for (e = e.child; e !== null; ) {
    var t = pc(e);
    if (t !== null) return t;
    e = e.sibling;
  }
  return null;
}
var mc = Be.unstable_scheduleCallback, La = Be.unstable_cancelCallback, hp = Be.unstable_shouldYield, gp = Be.unstable_requestPaint, ue = Be.unstable_now, vp = Be.unstable_getCurrentPriorityLevel, ks = Be.unstable_ImmediatePriority, hc = Be.unstable_UserBlockingPriority, Jl = Be.unstable_NormalPriority, yp = Be.unstable_LowPriority, gc = Be.unstable_IdlePriority, xi = null, ut = null;
function kp(e) {
  if (ut && typeof ut.onCommitFiberRoot == "function") try {
    ut.onCommitFiberRoot(xi, e, void 0, (e.current.flags & 128) === 128);
  } catch {
  }
}
var nt = Math.clz32 ? Math.clz32 : _p, xp = Math.log, wp = Math.LN2;
function _p(e) {
  return e >>>= 0, e === 0 ? 32 : 31 - (xp(e) / wp | 0) | 0;
}
var kl = 64, xl = 4194304;
function kr(e) {
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
function ql(e, t) {
  var n = e.pendingLanes;
  if (n === 0) return 0;
  var r = 0, l = e.suspendedLanes, i = e.pingedLanes, o = n & 268435455;
  if (o !== 0) {
    var a = o & ~l;
    a !== 0 ? r = kr(a) : (i &= o, i !== 0 && (r = kr(i)));
  } else o = n & ~l, o !== 0 ? r = kr(o) : i !== 0 && (r = kr(i));
  if (r === 0) return 0;
  if (t !== 0 && t !== r && !(t & l) && (l = r & -r, i = t & -t, l >= i || l === 16 && (i & 4194240) !== 0)) return t;
  if (r & 4 && (r |= n & 16), t = e.entangledLanes, t !== 0) for (e = e.entanglements, t &= r; 0 < t; ) n = 31 - nt(t), l = 1 << n, r |= e[n], t &= ~l;
  return r;
}
function Sp(e, t) {
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
function jp(e, t) {
  for (var n = e.suspendedLanes, r = e.pingedLanes, l = e.expirationTimes, i = e.pendingLanes; 0 < i; ) {
    var o = 31 - nt(i), a = 1 << o, u = l[o];
    u === -1 ? (!(a & n) || a & r) && (l[o] = Sp(a, t)) : u <= t && (e.expiredLanes |= a), i &= ~a;
  }
}
function Lo(e) {
  return e = e.pendingLanes & -1073741825, e !== 0 ? e : e & 1073741824 ? 1073741824 : 0;
}
function vc() {
  var e = kl;
  return kl <<= 1, !(kl & 4194240) && (kl = 64), e;
}
function Hi(e) {
  for (var t = [], n = 0; 31 > n; n++) t.push(e);
  return t;
}
function Zr(e, t, n) {
  e.pendingLanes |= t, t !== 536870912 && (e.suspendedLanes = 0, e.pingedLanes = 0), e = e.eventTimes, t = 31 - nt(t), e[t] = n;
}
function Ep(e, t) {
  var n = e.pendingLanes & ~t;
  e.pendingLanes = t, e.suspendedLanes = 0, e.pingedLanes = 0, e.expiredLanes &= t, e.mutableReadLanes &= t, e.entangledLanes &= t, t = e.entanglements;
  var r = e.eventTimes;
  for (e = e.expirationTimes; 0 < n; ) {
    var l = 31 - nt(n), i = 1 << l;
    t[l] = 0, r[l] = -1, e[l] = -1, n &= ~i;
  }
}
function xs(e, t) {
  var n = e.entangledLanes |= t;
  for (e = e.entanglements; n; ) {
    var r = 31 - nt(n), l = 1 << r;
    l & t | e[r] & t && (e[r] |= t), n &= ~l;
  }
}
var X = 0;
function yc(e) {
  return e &= -e, 1 < e ? 4 < e ? e & 268435455 ? 16 : 536870912 : 4 : 1;
}
var kc, ws, xc, wc, _c, Wo = !1, wl = [], Mt = null, Rt = null, Bt = null, Dr = /* @__PURE__ */ new Map(), Or = /* @__PURE__ */ new Map(), Ft = [], Tp = "mousedown mouseup touchcancel touchend touchstart auxclick dblclick pointercancel pointerdown pointerup dragend dragstart drop compositionend compositionstart keydown keypress keyup input textInput copy cut paste click change contextmenu reset submit".split(" ");
function Wa(e, t) {
  switch (e) {
    case "focusin":
    case "focusout":
      Mt = null;
      break;
    case "dragenter":
    case "dragleave":
      Rt = null;
      break;
    case "mouseover":
    case "mouseout":
      Bt = null;
      break;
    case "pointerover":
    case "pointerout":
      Dr.delete(t.pointerId);
      break;
    case "gotpointercapture":
    case "lostpointercapture":
      Or.delete(t.pointerId);
  }
}
function ur(e, t, n, r, l, i) {
  return e === null || e.nativeEvent !== i ? (e = { blockedOn: t, domEventName: n, eventSystemFlags: r, nativeEvent: i, targetContainers: [l] }, t !== null && (t = qr(t), t !== null && ws(t)), e) : (e.eventSystemFlags |= r, t = e.targetContainers, l !== null && t.indexOf(l) === -1 && t.push(l), e);
}
function zp(e, t, n, r, l) {
  switch (t) {
    case "focusin":
      return Mt = ur(Mt, e, t, n, r, l), !0;
    case "dragenter":
      return Rt = ur(Rt, e, t, n, r, l), !0;
    case "mouseover":
      return Bt = ur(Bt, e, t, n, r, l), !0;
    case "pointerover":
      var i = l.pointerId;
      return Dr.set(i, ur(Dr.get(i) || null, e, t, n, r, l)), !0;
    case "gotpointercapture":
      return i = l.pointerId, Or.set(i, ur(Or.get(i) || null, e, t, n, r, l)), !0;
  }
  return !1;
}
function Sc(e) {
  var t = rn(e.target);
  if (t !== null) {
    var n = hn(t);
    if (n !== null) {
      if (t = n.tag, t === 13) {
        if (t = dc(n), t !== null) {
          e.blockedOn = t, _c(e.priority, function() {
            xc(n);
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
function Il(e) {
  if (e.blockedOn !== null) return !1;
  for (var t = e.targetContainers; 0 < t.length; ) {
    var n = Fo(e.domEventName, e.eventSystemFlags, t[0], e.nativeEvent);
    if (n === null) {
      n = e.nativeEvent;
      var r = new n.constructor(n.type, n);
      No = r, n.target.dispatchEvent(r), No = null;
    } else return t = qr(n), t !== null && ws(t), e.blockedOn = n, !1;
    t.shift();
  }
  return !0;
}
function Fa(e, t, n) {
  Il(e) && n.delete(t);
}
function Np() {
  Wo = !1, Mt !== null && Il(Mt) && (Mt = null), Rt !== null && Il(Rt) && (Rt = null), Bt !== null && Il(Bt) && (Bt = null), Dr.forEach(Fa), Or.forEach(Fa);
}
function cr(e, t) {
  e.blockedOn === t && (e.blockedOn = null, Wo || (Wo = !0, Be.unstable_scheduleCallback(Be.unstable_NormalPriority, Np)));
}
function Ir(e) {
  function t(l) {
    return cr(l, e);
  }
  if (0 < wl.length) {
    cr(wl[0], e);
    for (var n = 1; n < wl.length; n++) {
      var r = wl[n];
      r.blockedOn === e && (r.blockedOn = null);
    }
  }
  for (Mt !== null && cr(Mt, e), Rt !== null && cr(Rt, e), Bt !== null && cr(Bt, e), Dr.forEach(t), Or.forEach(t), n = 0; n < Ft.length; n++) r = Ft[n], r.blockedOn === e && (r.blockedOn = null);
  for (; 0 < Ft.length && (n = Ft[0], n.blockedOn === null); ) Sc(n), n.blockedOn === null && Ft.shift();
}
var Mn = St.ReactCurrentBatchConfig, ei = !0;
function Cp(e, t, n, r) {
  var l = X, i = Mn.transition;
  Mn.transition = null;
  try {
    X = 1, _s(e, t, n, r);
  } finally {
    X = l, Mn.transition = i;
  }
}
function Pp(e, t, n, r) {
  var l = X, i = Mn.transition;
  Mn.transition = null;
  try {
    X = 4, _s(e, t, n, r);
  } finally {
    X = l, Mn.transition = i;
  }
}
function _s(e, t, n, r) {
  if (ei) {
    var l = Fo(e, t, n, r);
    if (l === null) to(e, t, r, ti, n), Wa(e, r);
    else if (zp(l, e, t, n, r)) r.stopPropagation();
    else if (Wa(e, r), t & 4 && -1 < Tp.indexOf(e)) {
      for (; l !== null; ) {
        var i = qr(l);
        if (i !== null && kc(i), i = Fo(e, t, n, r), i === null && to(e, t, r, ti, n), i === l) break;
        l = i;
      }
      l !== null && r.stopPropagation();
    } else to(e, t, r, null, n);
  }
}
var ti = null;
function Fo(e, t, n, r) {
  if (ti = null, e = ys(r), e = rn(e), e !== null) if (t = hn(e), t === null) e = null;
  else if (n = t.tag, n === 13) {
    if (e = dc(t), e !== null) return e;
    e = null;
  } else if (n === 3) {
    if (t.stateNode.current.memoizedState.isDehydrated) return t.tag === 3 ? t.stateNode.containerInfo : null;
    e = null;
  } else t !== e && (e = null);
  return ti = e, null;
}
function jc(e) {
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
      switch (vp()) {
        case ks:
          return 1;
        case hc:
          return 4;
        case Jl:
        case yp:
          return 16;
        case gc:
          return 536870912;
        default:
          return 16;
      }
    default:
      return 16;
  }
}
var Ot = null, Ss = null, Ml = null;
function Ec() {
  if (Ml) return Ml;
  var e, t = Ss, n = t.length, r, l = "value" in Ot ? Ot.value : Ot.textContent, i = l.length;
  for (e = 0; e < n && t[e] === l[e]; e++) ;
  var o = n - e;
  for (r = 1; r <= o && t[n - r] === l[i - r]; r++) ;
  return Ml = l.slice(e, 1 < r ? 1 - r : void 0);
}
function Rl(e) {
  var t = e.keyCode;
  return "charCode" in e ? (e = e.charCode, e === 0 && t === 13 && (e = 13)) : e = t, e === 10 && (e = 13), 32 <= e || e === 13 ? e : 0;
}
function _l() {
  return !0;
}
function Da() {
  return !1;
}
function Ue(e) {
  function t(n, r, l, i, o) {
    this._reactName = n, this._targetInst = l, this.type = r, this.nativeEvent = i, this.target = o, this.currentTarget = null;
    for (var a in e) e.hasOwnProperty(a) && (n = e[a], this[a] = n ? n(i) : i[a]);
    return this.isDefaultPrevented = (i.defaultPrevented != null ? i.defaultPrevented : i.returnValue === !1) ? _l : Da, this.isPropagationStopped = Da, this;
  }
  return ie(t.prototype, { preventDefault: function() {
    this.defaultPrevented = !0;
    var n = this.nativeEvent;
    n && (n.preventDefault ? n.preventDefault() : typeof n.returnValue != "unknown" && (n.returnValue = !1), this.isDefaultPrevented = _l);
  }, stopPropagation: function() {
    var n = this.nativeEvent;
    n && (n.stopPropagation ? n.stopPropagation() : typeof n.cancelBubble != "unknown" && (n.cancelBubble = !0), this.isPropagationStopped = _l);
  }, persist: function() {
  }, isPersistent: _l }), t;
}
var Zn = { eventPhase: 0, bubbles: 0, cancelable: 0, timeStamp: function(e) {
  return e.timeStamp || Date.now();
}, defaultPrevented: 0, isTrusted: 0 }, js = Ue(Zn), Jr = ie({}, Zn, { view: 0, detail: 0 }), Ap = Ue(Jr), Qi, bi, dr, wi = ie({}, Jr, { screenX: 0, screenY: 0, clientX: 0, clientY: 0, pageX: 0, pageY: 0, ctrlKey: 0, shiftKey: 0, altKey: 0, metaKey: 0, getModifierState: Es, button: 0, buttons: 0, relatedTarget: function(e) {
  return e.relatedTarget === void 0 ? e.fromElement === e.srcElement ? e.toElement : e.fromElement : e.relatedTarget;
}, movementX: function(e) {
  return "movementX" in e ? e.movementX : (e !== dr && (dr && e.type === "mousemove" ? (Qi = e.screenX - dr.screenX, bi = e.screenY - dr.screenY) : bi = Qi = 0, dr = e), Qi);
}, movementY: function(e) {
  return "movementY" in e ? e.movementY : bi;
} }), Oa = Ue(wi), Lp = ie({}, wi, { dataTransfer: 0 }), Wp = Ue(Lp), Fp = ie({}, Jr, { relatedTarget: 0 }), Gi = Ue(Fp), Dp = ie({}, Zn, { animationName: 0, elapsedTime: 0, pseudoElement: 0 }), Op = Ue(Dp), Ip = ie({}, Zn, { clipboardData: function(e) {
  return "clipboardData" in e ? e.clipboardData : window.clipboardData;
} }), Mp = Ue(Ip), Rp = ie({}, Zn, { data: 0 }), Ia = Ue(Rp), Bp = {
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
}, $p = {
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
}, Up = { Alt: "altKey", Control: "ctrlKey", Meta: "metaKey", Shift: "shiftKey" };
function Kp(e) {
  var t = this.nativeEvent;
  return t.getModifierState ? t.getModifierState(e) : (e = Up[e]) ? !!t[e] : !1;
}
function Es() {
  return Kp;
}
var Vp = ie({}, Jr, { key: function(e) {
  if (e.key) {
    var t = Bp[e.key] || e.key;
    if (t !== "Unidentified") return t;
  }
  return e.type === "keypress" ? (e = Rl(e), e === 13 ? "Enter" : String.fromCharCode(e)) : e.type === "keydown" || e.type === "keyup" ? $p[e.keyCode] || "Unidentified" : "";
}, code: 0, location: 0, ctrlKey: 0, shiftKey: 0, altKey: 0, metaKey: 0, repeat: 0, locale: 0, getModifierState: Es, charCode: function(e) {
  return e.type === "keypress" ? Rl(e) : 0;
}, keyCode: function(e) {
  return e.type === "keydown" || e.type === "keyup" ? e.keyCode : 0;
}, which: function(e) {
  return e.type === "keypress" ? Rl(e) : e.type === "keydown" || e.type === "keyup" ? e.keyCode : 0;
} }), Hp = Ue(Vp), Qp = ie({}, wi, { pointerId: 0, width: 0, height: 0, pressure: 0, tangentialPressure: 0, tiltX: 0, tiltY: 0, twist: 0, pointerType: 0, isPrimary: 0 }), Ma = Ue(Qp), bp = ie({}, Jr, { touches: 0, targetTouches: 0, changedTouches: 0, altKey: 0, metaKey: 0, ctrlKey: 0, shiftKey: 0, getModifierState: Es }), Gp = Ue(bp), Xp = ie({}, Zn, { propertyName: 0, elapsedTime: 0, pseudoElement: 0 }), Yp = Ue(Xp), Zp = ie({}, wi, {
  deltaX: function(e) {
    return "deltaX" in e ? e.deltaX : "wheelDeltaX" in e ? -e.wheelDeltaX : 0;
  },
  deltaY: function(e) {
    return "deltaY" in e ? e.deltaY : "wheelDeltaY" in e ? -e.wheelDeltaY : "wheelDelta" in e ? -e.wheelDelta : 0;
  },
  deltaZ: 0,
  deltaMode: 0
}), Jp = Ue(Zp), qp = [9, 13, 27, 32], Ts = kt && "CompositionEvent" in window, jr = null;
kt && "documentMode" in document && (jr = document.documentMode);
var em = kt && "TextEvent" in window && !jr, Tc = kt && (!Ts || jr && 8 < jr && 11 >= jr), Ra = " ", Ba = !1;
function zc(e, t) {
  switch (e) {
    case "keyup":
      return qp.indexOf(t.keyCode) !== -1;
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
function Nc(e) {
  return e = e.detail, typeof e == "object" && "data" in e ? e.data : null;
}
var En = !1;
function tm(e, t) {
  switch (e) {
    case "compositionend":
      return Nc(t);
    case "keypress":
      return t.which !== 32 ? null : (Ba = !0, Ra);
    case "textInput":
      return e = t.data, e === Ra && Ba ? null : e;
    default:
      return null;
  }
}
function nm(e, t) {
  if (En) return e === "compositionend" || !Ts && zc(e, t) ? (e = Ec(), Ml = Ss = Ot = null, En = !1, e) : null;
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
      return Tc && t.locale !== "ko" ? null : t.data;
    default:
      return null;
  }
}
var rm = { color: !0, date: !0, datetime: !0, "datetime-local": !0, email: !0, month: !0, number: !0, password: !0, range: !0, search: !0, tel: !0, text: !0, time: !0, url: !0, week: !0 };
function $a(e) {
  var t = e && e.nodeName && e.nodeName.toLowerCase();
  return t === "input" ? !!rm[e.type] : t === "textarea";
}
function Cc(e, t, n, r) {
  oc(r), t = ni(t, "onChange"), 0 < t.length && (n = new js("onChange", "change", null, n, r), e.push({ event: n, listeners: t }));
}
var Er = null, Mr = null;
function lm(e) {
  Bc(e, 0);
}
function _i(e) {
  var t = Nn(e);
  if (qu(t)) return e;
}
function im(e, t) {
  if (e === "change") return t;
}
var Pc = !1;
if (kt) {
  var Xi;
  if (kt) {
    var Yi = "oninput" in document;
    if (!Yi) {
      var Ua = document.createElement("div");
      Ua.setAttribute("oninput", "return;"), Yi = typeof Ua.oninput == "function";
    }
    Xi = Yi;
  } else Xi = !1;
  Pc = Xi && (!document.documentMode || 9 < document.documentMode);
}
function Ka() {
  Er && (Er.detachEvent("onpropertychange", Ac), Mr = Er = null);
}
function Ac(e) {
  if (e.propertyName === "value" && _i(Mr)) {
    var t = [];
    Cc(t, Mr, e, ys(e)), cc(lm, t);
  }
}
function om(e, t, n) {
  e === "focusin" ? (Ka(), Er = t, Mr = n, Er.attachEvent("onpropertychange", Ac)) : e === "focusout" && Ka();
}
function sm(e) {
  if (e === "selectionchange" || e === "keyup" || e === "keydown") return _i(Mr);
}
function am(e, t) {
  if (e === "click") return _i(t);
}
function um(e, t) {
  if (e === "input" || e === "change") return _i(t);
}
function cm(e, t) {
  return e === t && (e !== 0 || 1 / e === 1 / t) || e !== e && t !== t;
}
var lt = typeof Object.is == "function" ? Object.is : cm;
function Rr(e, t) {
  if (lt(e, t)) return !0;
  if (typeof e != "object" || e === null || typeof t != "object" || t === null) return !1;
  var n = Object.keys(e), r = Object.keys(t);
  if (n.length !== r.length) return !1;
  for (r = 0; r < n.length; r++) {
    var l = n[r];
    if (!go.call(t, l) || !lt(e[l], t[l])) return !1;
  }
  return !0;
}
function Va(e) {
  for (; e && e.firstChild; ) e = e.firstChild;
  return e;
}
function Ha(e, t) {
  var n = Va(e);
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
    n = Va(n);
  }
}
function Lc(e, t) {
  return e && t ? e === t ? !0 : e && e.nodeType === 3 ? !1 : t && t.nodeType === 3 ? Lc(e, t.parentNode) : "contains" in e ? e.contains(t) : e.compareDocumentPosition ? !!(e.compareDocumentPosition(t) & 16) : !1 : !1;
}
function Wc() {
  for (var e = window, t = Xl(); t instanceof e.HTMLIFrameElement; ) {
    try {
      var n = typeof t.contentWindow.location.href == "string";
    } catch {
      n = !1;
    }
    if (n) e = t.contentWindow;
    else break;
    t = Xl(e.document);
  }
  return t;
}
function zs(e) {
  var t = e && e.nodeName && e.nodeName.toLowerCase();
  return t && (t === "input" && (e.type === "text" || e.type === "search" || e.type === "tel" || e.type === "url" || e.type === "password") || t === "textarea" || e.contentEditable === "true");
}
function dm(e) {
  var t = Wc(), n = e.focusedElem, r = e.selectionRange;
  if (t !== n && n && n.ownerDocument && Lc(n.ownerDocument.documentElement, n)) {
    if (r !== null && zs(n)) {
      if (t = r.start, e = r.end, e === void 0 && (e = t), "selectionStart" in n) n.selectionStart = t, n.selectionEnd = Math.min(e, n.value.length);
      else if (e = (t = n.ownerDocument || document) && t.defaultView || window, e.getSelection) {
        e = e.getSelection();
        var l = n.textContent.length, i = Math.min(r.start, l);
        r = r.end === void 0 ? i : Math.min(r.end, l), !e.extend && i > r && (l = r, r = i, i = l), l = Ha(n, i);
        var o = Ha(
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
var fm = kt && "documentMode" in document && 11 >= document.documentMode, Tn = null, Do = null, Tr = null, Oo = !1;
function Qa(e, t, n) {
  var r = n.window === n ? n.document : n.nodeType === 9 ? n : n.ownerDocument;
  Oo || Tn == null || Tn !== Xl(r) || (r = Tn, "selectionStart" in r && zs(r) ? r = { start: r.selectionStart, end: r.selectionEnd } : (r = (r.ownerDocument && r.ownerDocument.defaultView || window).getSelection(), r = { anchorNode: r.anchorNode, anchorOffset: r.anchorOffset, focusNode: r.focusNode, focusOffset: r.focusOffset }), Tr && Rr(Tr, r) || (Tr = r, r = ni(Do, "onSelect"), 0 < r.length && (t = new js("onSelect", "select", null, t, n), e.push({ event: t, listeners: r }), t.target = Tn)));
}
function Sl(e, t) {
  var n = {};
  return n[e.toLowerCase()] = t.toLowerCase(), n["Webkit" + e] = "webkit" + t, n["Moz" + e] = "moz" + t, n;
}
var zn = { animationend: Sl("Animation", "AnimationEnd"), animationiteration: Sl("Animation", "AnimationIteration"), animationstart: Sl("Animation", "AnimationStart"), transitionend: Sl("Transition", "TransitionEnd") }, Zi = {}, Fc = {};
kt && (Fc = document.createElement("div").style, "AnimationEvent" in window || (delete zn.animationend.animation, delete zn.animationiteration.animation, delete zn.animationstart.animation), "TransitionEvent" in window || delete zn.transitionend.transition);
function Si(e) {
  if (Zi[e]) return Zi[e];
  if (!zn[e]) return e;
  var t = zn[e], n;
  for (n in t) if (t.hasOwnProperty(n) && n in Fc) return Zi[e] = t[n];
  return e;
}
var Dc = Si("animationend"), Oc = Si("animationiteration"), Ic = Si("animationstart"), Mc = Si("transitionend"), Rc = /* @__PURE__ */ new Map(), ba = "abort auxClick cancel canPlay canPlayThrough click close contextMenu copy cut drag dragEnd dragEnter dragExit dragLeave dragOver dragStart drop durationChange emptied encrypted ended error gotPointerCapture input invalid keyDown keyPress keyUp load loadedData loadedMetadata loadStart lostPointerCapture mouseDown mouseMove mouseOut mouseOver mouseUp paste pause play playing pointerCancel pointerDown pointerMove pointerOut pointerOver pointerUp progress rateChange reset resize seeked seeking stalled submit suspend timeUpdate touchCancel touchEnd touchStart volumeChange scroll toggle touchMove waiting wheel".split(" ");
function Gt(e, t) {
  Rc.set(e, t), mn(t, [e]);
}
for (var Ji = 0; Ji < ba.length; Ji++) {
  var qi = ba[Ji], pm = qi.toLowerCase(), mm = qi[0].toUpperCase() + qi.slice(1);
  Gt(pm, "on" + mm);
}
Gt(Dc, "onAnimationEnd");
Gt(Oc, "onAnimationIteration");
Gt(Ic, "onAnimationStart");
Gt("dblclick", "onDoubleClick");
Gt("focusin", "onFocus");
Gt("focusout", "onBlur");
Gt(Mc, "onTransitionEnd");
$n("onMouseEnter", ["mouseout", "mouseover"]);
$n("onMouseLeave", ["mouseout", "mouseover"]);
$n("onPointerEnter", ["pointerout", "pointerover"]);
$n("onPointerLeave", ["pointerout", "pointerover"]);
mn("onChange", "change click focusin focusout input keydown keyup selectionchange".split(" "));
mn("onSelect", "focusout contextmenu dragend focusin keydown keyup mousedown mouseup selectionchange".split(" "));
mn("onBeforeInput", ["compositionend", "keypress", "textInput", "paste"]);
mn("onCompositionEnd", "compositionend focusout keydown keypress keyup mousedown".split(" "));
mn("onCompositionStart", "compositionstart focusout keydown keypress keyup mousedown".split(" "));
mn("onCompositionUpdate", "compositionupdate focusout keydown keypress keyup mousedown".split(" "));
var xr = "abort canplay canplaythrough durationchange emptied encrypted ended error loadeddata loadedmetadata loadstart pause play playing progress ratechange resize seeked seeking stalled suspend timeupdate volumechange waiting".split(" "), hm = new Set("cancel close invalid load scroll toggle".split(" ").concat(xr));
function Ga(e, t, n) {
  var r = e.type || "unknown-event";
  e.currentTarget = n, pp(r, t, void 0, e), e.currentTarget = null;
}
function Bc(e, t) {
  t = (t & 4) !== 0;
  for (var n = 0; n < e.length; n++) {
    var r = e[n], l = r.event;
    r = r.listeners;
    e: {
      var i = void 0;
      if (t) for (var o = r.length - 1; 0 <= o; o--) {
        var a = r[o], u = a.instance, d = a.currentTarget;
        if (a = a.listener, u !== i && l.isPropagationStopped()) break e;
        Ga(l, a, d), i = u;
      }
      else for (o = 0; o < r.length; o++) {
        if (a = r[o], u = a.instance, d = a.currentTarget, a = a.listener, u !== i && l.isPropagationStopped()) break e;
        Ga(l, a, d), i = u;
      }
    }
  }
  if (Zl) throw e = Ao, Zl = !1, Ao = null, e;
}
function Z(e, t) {
  var n = t[$o];
  n === void 0 && (n = t[$o] = /* @__PURE__ */ new Set());
  var r = e + "__bubble";
  n.has(r) || ($c(t, e, 2, !1), n.add(r));
}
function eo(e, t, n) {
  var r = 0;
  t && (r |= 4), $c(n, e, r, t);
}
var jl = "_reactListening" + Math.random().toString(36).slice(2);
function Br(e) {
  if (!e[jl]) {
    e[jl] = !0, Gu.forEach(function(n) {
      n !== "selectionchange" && (hm.has(n) || eo(n, !1, e), eo(n, !0, e));
    });
    var t = e.nodeType === 9 ? e : e.ownerDocument;
    t === null || t[jl] || (t[jl] = !0, eo("selectionchange", !1, t));
  }
}
function $c(e, t, n, r) {
  switch (jc(t)) {
    case 1:
      var l = Cp;
      break;
    case 4:
      l = Pp;
      break;
    default:
      l = _s;
  }
  n = l.bind(null, t, n, e), l = void 0, !Po || t !== "touchstart" && t !== "touchmove" && t !== "wheel" || (l = !0), r ? l !== void 0 ? e.addEventListener(t, n, { capture: !0, passive: l }) : e.addEventListener(t, n, !0) : l !== void 0 ? e.addEventListener(t, n, { passive: l }) : e.addEventListener(t, n, !1);
}
function to(e, t, n, r, l) {
  var i = r;
  if (!(t & 1) && !(t & 2) && r !== null) e: for (; ; ) {
    if (r === null) return;
    var o = r.tag;
    if (o === 3 || o === 4) {
      var a = r.stateNode.containerInfo;
      if (a === l || a.nodeType === 8 && a.parentNode === l) break;
      if (o === 4) for (o = r.return; o !== null; ) {
        var u = o.tag;
        if ((u === 3 || u === 4) && (u = o.stateNode.containerInfo, u === l || u.nodeType === 8 && u.parentNode === l)) return;
        o = o.return;
      }
      for (; a !== null; ) {
        if (o = rn(a), o === null) return;
        if (u = o.tag, u === 5 || u === 6) {
          r = i = o;
          continue e;
        }
        a = a.parentNode;
      }
    }
    r = r.return;
  }
  cc(function() {
    var d = i, y = ys(n), g = [];
    e: {
      var h = Rc.get(e);
      if (h !== void 0) {
        var k = js, E = e;
        switch (e) {
          case "keypress":
            if (Rl(n) === 0) break e;
          case "keydown":
          case "keyup":
            k = Hp;
            break;
          case "focusin":
            E = "focus", k = Gi;
            break;
          case "focusout":
            E = "blur", k = Gi;
            break;
          case "beforeblur":
          case "afterblur":
            k = Gi;
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
            k = Oa;
            break;
          case "drag":
          case "dragend":
          case "dragenter":
          case "dragexit":
          case "dragleave":
          case "dragover":
          case "dragstart":
          case "drop":
            k = Wp;
            break;
          case "touchcancel":
          case "touchend":
          case "touchmove":
          case "touchstart":
            k = Gp;
            break;
          case Dc:
          case Oc:
          case Ic:
            k = Op;
            break;
          case Mc:
            k = Yp;
            break;
          case "scroll":
            k = Ap;
            break;
          case "wheel":
            k = Jp;
            break;
          case "copy":
          case "cut":
          case "paste":
            k = Mp;
            break;
          case "gotpointercapture":
          case "lostpointercapture":
          case "pointercancel":
          case "pointerdown":
          case "pointermove":
          case "pointerout":
          case "pointerover":
          case "pointerup":
            k = Ma;
        }
        var z = (t & 4) !== 0, Q = !z && e === "scroll", p = z ? h !== null ? h + "Capture" : null : h;
        z = [];
        for (var c = d, m; c !== null; ) {
          m = c;
          var _ = m.stateNode;
          if (m.tag === 5 && _ !== null && (m = _, p !== null && (_ = Fr(c, p), _ != null && z.push($r(c, _, m)))), Q) break;
          c = c.return;
        }
        0 < z.length && (h = new k(h, E, null, n, y), g.push({ event: h, listeners: z }));
      }
    }
    if (!(t & 7)) {
      e: {
        if (h = e === "mouseover" || e === "pointerover", k = e === "mouseout" || e === "pointerout", h && n !== No && (E = n.relatedTarget || n.fromElement) && (rn(E) || E[xt])) break e;
        if ((k || h) && (h = y.window === y ? y : (h = y.ownerDocument) ? h.defaultView || h.parentWindow : window, k ? (E = n.relatedTarget || n.toElement, k = d, E = E ? rn(E) : null, E !== null && (Q = hn(E), E !== Q || E.tag !== 5 && E.tag !== 6) && (E = null)) : (k = null, E = d), k !== E)) {
          if (z = Oa, _ = "onMouseLeave", p = "onMouseEnter", c = "mouse", (e === "pointerout" || e === "pointerover") && (z = Ma, _ = "onPointerLeave", p = "onPointerEnter", c = "pointer"), Q = k == null ? h : Nn(k), m = E == null ? h : Nn(E), h = new z(_, c + "leave", k, n, y), h.target = Q, h.relatedTarget = m, _ = null, rn(y) === d && (z = new z(p, c + "enter", E, n, y), z.target = m, z.relatedTarget = Q, _ = z), Q = _, k && E) t: {
            for (z = k, p = E, c = 0, m = z; m; m = _n(m)) c++;
            for (m = 0, _ = p; _; _ = _n(_)) m++;
            for (; 0 < c - m; ) z = _n(z), c--;
            for (; 0 < m - c; ) p = _n(p), m--;
            for (; c--; ) {
              if (z === p || p !== null && z === p.alternate) break t;
              z = _n(z), p = _n(p);
            }
            z = null;
          }
          else z = null;
          k !== null && Xa(g, h, k, z, !1), E !== null && Q !== null && Xa(g, Q, E, z, !0);
        }
      }
      e: {
        if (h = d ? Nn(d) : window, k = h.nodeName && h.nodeName.toLowerCase(), k === "select" || k === "input" && h.type === "file") var j = im;
        else if ($a(h)) if (Pc) j = um;
        else {
          j = sm;
          var A = om;
        }
        else (k = h.nodeName) && k.toLowerCase() === "input" && (h.type === "checkbox" || h.type === "radio") && (j = am);
        if (j && (j = j(e, d))) {
          Cc(g, j, n, y);
          break e;
        }
        A && A(e, h, d), e === "focusout" && (A = h._wrapperState) && A.controlled && h.type === "number" && So(h, "number", h.value);
      }
      switch (A = d ? Nn(d) : window, e) {
        case "focusin":
          ($a(A) || A.contentEditable === "true") && (Tn = A, Do = d, Tr = null);
          break;
        case "focusout":
          Tr = Do = Tn = null;
          break;
        case "mousedown":
          Oo = !0;
          break;
        case "contextmenu":
        case "mouseup":
        case "dragend":
          Oo = !1, Qa(g, n, y);
          break;
        case "selectionchange":
          if (fm) break;
        case "keydown":
        case "keyup":
          Qa(g, n, y);
      }
      var W;
      if (Ts) e: {
        switch (e) {
          case "compositionstart":
            var O = "onCompositionStart";
            break e;
          case "compositionend":
            O = "onCompositionEnd";
            break e;
          case "compositionupdate":
            O = "onCompositionUpdate";
            break e;
        }
        O = void 0;
      }
      else En ? zc(e, n) && (O = "onCompositionEnd") : e === "keydown" && n.keyCode === 229 && (O = "onCompositionStart");
      O && (Tc && n.locale !== "ko" && (En || O !== "onCompositionStart" ? O === "onCompositionEnd" && En && (W = Ec()) : (Ot = y, Ss = "value" in Ot ? Ot.value : Ot.textContent, En = !0)), A = ni(d, O), 0 < A.length && (O = new Ia(O, e, null, n, y), g.push({ event: O, listeners: A }), W ? O.data = W : (W = Nc(n), W !== null && (O.data = W)))), (W = em ? tm(e, n) : nm(e, n)) && (d = ni(d, "onBeforeInput"), 0 < d.length && (y = new Ia("onBeforeInput", "beforeinput", null, n, y), g.push({ event: y, listeners: d }), y.data = W));
    }
    Bc(g, t);
  });
}
function $r(e, t, n) {
  return { instance: e, listener: t, currentTarget: n };
}
function ni(e, t) {
  for (var n = t + "Capture", r = []; e !== null; ) {
    var l = e, i = l.stateNode;
    l.tag === 5 && i !== null && (l = i, i = Fr(e, n), i != null && r.unshift($r(e, i, l)), i = Fr(e, t), i != null && r.push($r(e, i, l))), e = e.return;
  }
  return r;
}
function _n(e) {
  if (e === null) return null;
  do
    e = e.return;
  while (e && e.tag !== 5);
  return e || null;
}
function Xa(e, t, n, r, l) {
  for (var i = t._reactName, o = []; n !== null && n !== r; ) {
    var a = n, u = a.alternate, d = a.stateNode;
    if (u !== null && u === r) break;
    a.tag === 5 && d !== null && (a = d, l ? (u = Fr(n, i), u != null && o.unshift($r(n, u, a))) : l || (u = Fr(n, i), u != null && o.push($r(n, u, a)))), n = n.return;
  }
  o.length !== 0 && e.push({ event: t, listeners: o });
}
var gm = /\r\n?/g, vm = /\u0000|\uFFFD/g;
function Ya(e) {
  return (typeof e == "string" ? e : "" + e).replace(gm, `
`).replace(vm, "");
}
function El(e, t, n) {
  if (t = Ya(t), Ya(e) !== t && n) throw Error(S(425));
}
function ri() {
}
var Io = null, Mo = null;
function Ro(e, t) {
  return e === "textarea" || e === "noscript" || typeof t.children == "string" || typeof t.children == "number" || typeof t.dangerouslySetInnerHTML == "object" && t.dangerouslySetInnerHTML !== null && t.dangerouslySetInnerHTML.__html != null;
}
var Bo = typeof setTimeout == "function" ? setTimeout : void 0, ym = typeof clearTimeout == "function" ? clearTimeout : void 0, Za = typeof Promise == "function" ? Promise : void 0, km = typeof queueMicrotask == "function" ? queueMicrotask : typeof Za < "u" ? function(e) {
  return Za.resolve(null).then(e).catch(xm);
} : Bo;
function xm(e) {
  setTimeout(function() {
    throw e;
  });
}
function no(e, t) {
  var n = t, r = 0;
  do {
    var l = n.nextSibling;
    if (e.removeChild(n), l && l.nodeType === 8) if (n = l.data, n === "/$") {
      if (r === 0) {
        e.removeChild(l), Ir(t);
        return;
      }
      r--;
    } else n !== "$" && n !== "$?" && n !== "$!" || r++;
    n = l;
  } while (n);
  Ir(t);
}
function $t(e) {
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
function Ja(e) {
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
var Jn = Math.random().toString(36).slice(2), at = "__reactFiber$" + Jn, Ur = "__reactProps$" + Jn, xt = "__reactContainer$" + Jn, $o = "__reactEvents$" + Jn, wm = "__reactListeners$" + Jn, _m = "__reactHandles$" + Jn;
function rn(e) {
  var t = e[at];
  if (t) return t;
  for (var n = e.parentNode; n; ) {
    if (t = n[xt] || n[at]) {
      if (n = t.alternate, t.child !== null || n !== null && n.child !== null) for (e = Ja(e); e !== null; ) {
        if (n = e[at]) return n;
        e = Ja(e);
      }
      return t;
    }
    e = n, n = e.parentNode;
  }
  return null;
}
function qr(e) {
  return e = e[at] || e[xt], !e || e.tag !== 5 && e.tag !== 6 && e.tag !== 13 && e.tag !== 3 ? null : e;
}
function Nn(e) {
  if (e.tag === 5 || e.tag === 6) return e.stateNode;
  throw Error(S(33));
}
function ji(e) {
  return e[Ur] || null;
}
var Uo = [], Cn = -1;
function Xt(e) {
  return { current: e };
}
function J(e) {
  0 > Cn || (e.current = Uo[Cn], Uo[Cn] = null, Cn--);
}
function Y(e, t) {
  Cn++, Uo[Cn] = e.current, e.current = t;
}
var bt = {}, Ee = Xt(bt), Fe = Xt(!1), un = bt;
function Un(e, t) {
  var n = e.type.contextTypes;
  if (!n) return bt;
  var r = e.stateNode;
  if (r && r.__reactInternalMemoizedUnmaskedChildContext === t) return r.__reactInternalMemoizedMaskedChildContext;
  var l = {}, i;
  for (i in n) l[i] = t[i];
  return r && (e = e.stateNode, e.__reactInternalMemoizedUnmaskedChildContext = t, e.__reactInternalMemoizedMaskedChildContext = l), l;
}
function De(e) {
  return e = e.childContextTypes, e != null;
}
function li() {
  J(Fe), J(Ee);
}
function qa(e, t, n) {
  if (Ee.current !== bt) throw Error(S(168));
  Y(Ee, t), Y(Fe, n);
}
function Uc(e, t, n) {
  var r = e.stateNode;
  if (t = t.childContextTypes, typeof r.getChildContext != "function") return n;
  r = r.getChildContext();
  for (var l in r) if (!(l in t)) throw Error(S(108, op(e) || "Unknown", l));
  return ie({}, n, r);
}
function ii(e) {
  return e = (e = e.stateNode) && e.__reactInternalMemoizedMergedChildContext || bt, un = Ee.current, Y(Ee, e), Y(Fe, Fe.current), !0;
}
function eu(e, t, n) {
  var r = e.stateNode;
  if (!r) throw Error(S(169));
  n ? (e = Uc(e, t, un), r.__reactInternalMemoizedMergedChildContext = e, J(Fe), J(Ee), Y(Ee, e)) : J(Fe), Y(Fe, n);
}
var ht = null, Ei = !1, ro = !1;
function Kc(e) {
  ht === null ? ht = [e] : ht.push(e);
}
function Sm(e) {
  Ei = !0, Kc(e);
}
function Yt() {
  if (!ro && ht !== null) {
    ro = !0;
    var e = 0, t = X;
    try {
      var n = ht;
      for (X = 1; e < n.length; e++) {
        var r = n[e];
        do
          r = r(!0);
        while (r !== null);
      }
      ht = null, Ei = !1;
    } catch (l) {
      throw ht !== null && (ht = ht.slice(e + 1)), mc(ks, Yt), l;
    } finally {
      X = t, ro = !1;
    }
  }
  return null;
}
var Pn = [], An = 0, oi = null, si = 0, He = [], Qe = 0, cn = null, gt = 1, vt = "";
function en(e, t) {
  Pn[An++] = si, Pn[An++] = oi, oi = e, si = t;
}
function Vc(e, t, n) {
  He[Qe++] = gt, He[Qe++] = vt, He[Qe++] = cn, cn = e;
  var r = gt;
  e = vt;
  var l = 32 - nt(r) - 1;
  r &= ~(1 << l), n += 1;
  var i = 32 - nt(t) + l;
  if (30 < i) {
    var o = l - l % 5;
    i = (r & (1 << o) - 1).toString(32), r >>= o, l -= o, gt = 1 << 32 - nt(t) + l | n << l | r, vt = i + e;
  } else gt = 1 << i | n << l | r, vt = e;
}
function Ns(e) {
  e.return !== null && (en(e, 1), Vc(e, 1, 0));
}
function Cs(e) {
  for (; e === oi; ) oi = Pn[--An], Pn[An] = null, si = Pn[--An], Pn[An] = null;
  for (; e === cn; ) cn = He[--Qe], He[Qe] = null, vt = He[--Qe], He[Qe] = null, gt = He[--Qe], He[Qe] = null;
}
var Re = null, Me = null, q = !1, tt = null;
function Hc(e, t) {
  var n = be(5, null, null, 0);
  n.elementType = "DELETED", n.stateNode = t, n.return = e, t = e.deletions, t === null ? (e.deletions = [n], e.flags |= 16) : t.push(n);
}
function tu(e, t) {
  switch (e.tag) {
    case 5:
      var n = e.type;
      return t = t.nodeType !== 1 || n.toLowerCase() !== t.nodeName.toLowerCase() ? null : t, t !== null ? (e.stateNode = t, Re = e, Me = $t(t.firstChild), !0) : !1;
    case 6:
      return t = e.pendingProps === "" || t.nodeType !== 3 ? null : t, t !== null ? (e.stateNode = t, Re = e, Me = null, !0) : !1;
    case 13:
      return t = t.nodeType !== 8 ? null : t, t !== null ? (n = cn !== null ? { id: gt, overflow: vt } : null, e.memoizedState = { dehydrated: t, treeContext: n, retryLane: 1073741824 }, n = be(18, null, null, 0), n.stateNode = t, n.return = e, e.child = n, Re = e, Me = null, !0) : !1;
    default:
      return !1;
  }
}
function Ko(e) {
  return (e.mode & 1) !== 0 && (e.flags & 128) === 0;
}
function Vo(e) {
  if (q) {
    var t = Me;
    if (t) {
      var n = t;
      if (!tu(e, t)) {
        if (Ko(e)) throw Error(S(418));
        t = $t(n.nextSibling);
        var r = Re;
        t && tu(e, t) ? Hc(r, n) : (e.flags = e.flags & -4097 | 2, q = !1, Re = e);
      }
    } else {
      if (Ko(e)) throw Error(S(418));
      e.flags = e.flags & -4097 | 2, q = !1, Re = e;
    }
  }
}
function nu(e) {
  for (e = e.return; e !== null && e.tag !== 5 && e.tag !== 3 && e.tag !== 13; ) e = e.return;
  Re = e;
}
function Tl(e) {
  if (e !== Re) return !1;
  if (!q) return nu(e), q = !0, !1;
  var t;
  if ((t = e.tag !== 3) && !(t = e.tag !== 5) && (t = e.type, t = t !== "head" && t !== "body" && !Ro(e.type, e.memoizedProps)), t && (t = Me)) {
    if (Ko(e)) throw Qc(), Error(S(418));
    for (; t; ) Hc(e, t), t = $t(t.nextSibling);
  }
  if (nu(e), e.tag === 13) {
    if (e = e.memoizedState, e = e !== null ? e.dehydrated : null, !e) throw Error(S(317));
    e: {
      for (e = e.nextSibling, t = 0; e; ) {
        if (e.nodeType === 8) {
          var n = e.data;
          if (n === "/$") {
            if (t === 0) {
              Me = $t(e.nextSibling);
              break e;
            }
            t--;
          } else n !== "$" && n !== "$!" && n !== "$?" || t++;
        }
        e = e.nextSibling;
      }
      Me = null;
    }
  } else Me = Re ? $t(e.stateNode.nextSibling) : null;
  return !0;
}
function Qc() {
  for (var e = Me; e; ) e = $t(e.nextSibling);
}
function Kn() {
  Me = Re = null, q = !1;
}
function Ps(e) {
  tt === null ? tt = [e] : tt.push(e);
}
var jm = St.ReactCurrentBatchConfig;
function fr(e, t, n) {
  if (e = n.ref, e !== null && typeof e != "function" && typeof e != "object") {
    if (n._owner) {
      if (n = n._owner, n) {
        if (n.tag !== 1) throw Error(S(309));
        var r = n.stateNode;
      }
      if (!r) throw Error(S(147, e));
      var l = r, i = "" + e;
      return t !== null && t.ref !== null && typeof t.ref == "function" && t.ref._stringRef === i ? t.ref : (t = function(o) {
        var a = l.refs;
        o === null ? delete a[i] : a[i] = o;
      }, t._stringRef = i, t);
    }
    if (typeof e != "string") throw Error(S(284));
    if (!n._owner) throw Error(S(290, e));
  }
  return e;
}
function zl(e, t) {
  throw e = Object.prototype.toString.call(t), Error(S(31, e === "[object Object]" ? "object with keys {" + Object.keys(t).join(", ") + "}" : e));
}
function ru(e) {
  var t = e._init;
  return t(e._payload);
}
function bc(e) {
  function t(p, c) {
    if (e) {
      var m = p.deletions;
      m === null ? (p.deletions = [c], p.flags |= 16) : m.push(c);
    }
  }
  function n(p, c) {
    if (!e) return null;
    for (; c !== null; ) t(p, c), c = c.sibling;
    return null;
  }
  function r(p, c) {
    for (p = /* @__PURE__ */ new Map(); c !== null; ) c.key !== null ? p.set(c.key, c) : p.set(c.index, c), c = c.sibling;
    return p;
  }
  function l(p, c) {
    return p = Ht(p, c), p.index = 0, p.sibling = null, p;
  }
  function i(p, c, m) {
    return p.index = m, e ? (m = p.alternate, m !== null ? (m = m.index, m < c ? (p.flags |= 2, c) : m) : (p.flags |= 2, c)) : (p.flags |= 1048576, c);
  }
  function o(p) {
    return e && p.alternate === null && (p.flags |= 2), p;
  }
  function a(p, c, m, _) {
    return c === null || c.tag !== 6 ? (c = co(m, p.mode, _), c.return = p, c) : (c = l(c, m), c.return = p, c);
  }
  function u(p, c, m, _) {
    var j = m.type;
    return j === jn ? y(p, c, m.props.children, _, m.key) : c !== null && (c.elementType === j || typeof j == "object" && j !== null && j.$$typeof === Lt && ru(j) === c.type) ? (_ = l(c, m.props), _.ref = fr(p, c, m), _.return = p, _) : (_ = Ql(m.type, m.key, m.props, null, p.mode, _), _.ref = fr(p, c, m), _.return = p, _);
  }
  function d(p, c, m, _) {
    return c === null || c.tag !== 4 || c.stateNode.containerInfo !== m.containerInfo || c.stateNode.implementation !== m.implementation ? (c = fo(m, p.mode, _), c.return = p, c) : (c = l(c, m.children || []), c.return = p, c);
  }
  function y(p, c, m, _, j) {
    return c === null || c.tag !== 7 ? (c = an(m, p.mode, _, j), c.return = p, c) : (c = l(c, m), c.return = p, c);
  }
  function g(p, c, m) {
    if (typeof c == "string" && c !== "" || typeof c == "number") return c = co("" + c, p.mode, m), c.return = p, c;
    if (typeof c == "object" && c !== null) {
      switch (c.$$typeof) {
        case gl:
          return m = Ql(c.type, c.key, c.props, null, p.mode, m), m.ref = fr(p, null, c), m.return = p, m;
        case Sn:
          return c = fo(c, p.mode, m), c.return = p, c;
        case Lt:
          var _ = c._init;
          return g(p, _(c._payload), m);
      }
      if (yr(c) || sr(c)) return c = an(c, p.mode, m, null), c.return = p, c;
      zl(p, c);
    }
    return null;
  }
  function h(p, c, m, _) {
    var j = c !== null ? c.key : null;
    if (typeof m == "string" && m !== "" || typeof m == "number") return j !== null ? null : a(p, c, "" + m, _);
    if (typeof m == "object" && m !== null) {
      switch (m.$$typeof) {
        case gl:
          return m.key === j ? u(p, c, m, _) : null;
        case Sn:
          return m.key === j ? d(p, c, m, _) : null;
        case Lt:
          return j = m._init, h(
            p,
            c,
            j(m._payload),
            _
          );
      }
      if (yr(m) || sr(m)) return j !== null ? null : y(p, c, m, _, null);
      zl(p, m);
    }
    return null;
  }
  function k(p, c, m, _, j) {
    if (typeof _ == "string" && _ !== "" || typeof _ == "number") return p = p.get(m) || null, a(c, p, "" + _, j);
    if (typeof _ == "object" && _ !== null) {
      switch (_.$$typeof) {
        case gl:
          return p = p.get(_.key === null ? m : _.key) || null, u(c, p, _, j);
        case Sn:
          return p = p.get(_.key === null ? m : _.key) || null, d(c, p, _, j);
        case Lt:
          var A = _._init;
          return k(p, c, m, A(_._payload), j);
      }
      if (yr(_) || sr(_)) return p = p.get(m) || null, y(c, p, _, j, null);
      zl(c, _);
    }
    return null;
  }
  function E(p, c, m, _) {
    for (var j = null, A = null, W = c, O = c = 0, U = null; W !== null && O < m.length; O++) {
      W.index > O ? (U = W, W = null) : U = W.sibling;
      var $ = h(p, W, m[O], _);
      if ($ === null) {
        W === null && (W = U);
        break;
      }
      e && W && $.alternate === null && t(p, W), c = i($, c, O), A === null ? j = $ : A.sibling = $, A = $, W = U;
    }
    if (O === m.length) return n(p, W), q && en(p, O), j;
    if (W === null) {
      for (; O < m.length; O++) W = g(p, m[O], _), W !== null && (c = i(W, c, O), A === null ? j = W : A.sibling = W, A = W);
      return q && en(p, O), j;
    }
    for (W = r(p, W); O < m.length; O++) U = k(W, p, O, m[O], _), U !== null && (e && U.alternate !== null && W.delete(U.key === null ? O : U.key), c = i(U, c, O), A === null ? j = U : A.sibling = U, A = U);
    return e && W.forEach(function(ve) {
      return t(p, ve);
    }), q && en(p, O), j;
  }
  function z(p, c, m, _) {
    var j = sr(m);
    if (typeof j != "function") throw Error(S(150));
    if (m = j.call(m), m == null) throw Error(S(151));
    for (var A = j = null, W = c, O = c = 0, U = null, $ = m.next(); W !== null && !$.done; O++, $ = m.next()) {
      W.index > O ? (U = W, W = null) : U = W.sibling;
      var ve = h(p, W, $.value, _);
      if (ve === null) {
        W === null && (W = U);
        break;
      }
      e && W && ve.alternate === null && t(p, W), c = i(ve, c, O), A === null ? j = ve : A.sibling = ve, A = ve, W = U;
    }
    if ($.done) return n(
      p,
      W
    ), q && en(p, O), j;
    if (W === null) {
      for (; !$.done; O++, $ = m.next()) $ = g(p, $.value, _), $ !== null && (c = i($, c, O), A === null ? j = $ : A.sibling = $, A = $);
      return q && en(p, O), j;
    }
    for (W = r(p, W); !$.done; O++, $ = m.next()) $ = k(W, p, O, $.value, _), $ !== null && (e && $.alternate !== null && W.delete($.key === null ? O : $.key), c = i($, c, O), A === null ? j = $ : A.sibling = $, A = $);
    return e && W.forEach(function(jt) {
      return t(p, jt);
    }), q && en(p, O), j;
  }
  function Q(p, c, m, _) {
    if (typeof m == "object" && m !== null && m.type === jn && m.key === null && (m = m.props.children), typeof m == "object" && m !== null) {
      switch (m.$$typeof) {
        case gl:
          e: {
            for (var j = m.key, A = c; A !== null; ) {
              if (A.key === j) {
                if (j = m.type, j === jn) {
                  if (A.tag === 7) {
                    n(p, A.sibling), c = l(A, m.props.children), c.return = p, p = c;
                    break e;
                  }
                } else if (A.elementType === j || typeof j == "object" && j !== null && j.$$typeof === Lt && ru(j) === A.type) {
                  n(p, A.sibling), c = l(A, m.props), c.ref = fr(p, A, m), c.return = p, p = c;
                  break e;
                }
                n(p, A);
                break;
              } else t(p, A);
              A = A.sibling;
            }
            m.type === jn ? (c = an(m.props.children, p.mode, _, m.key), c.return = p, p = c) : (_ = Ql(m.type, m.key, m.props, null, p.mode, _), _.ref = fr(p, c, m), _.return = p, p = _);
          }
          return o(p);
        case Sn:
          e: {
            for (A = m.key; c !== null; ) {
              if (c.key === A) if (c.tag === 4 && c.stateNode.containerInfo === m.containerInfo && c.stateNode.implementation === m.implementation) {
                n(p, c.sibling), c = l(c, m.children || []), c.return = p, p = c;
                break e;
              } else {
                n(p, c);
                break;
              }
              else t(p, c);
              c = c.sibling;
            }
            c = fo(m, p.mode, _), c.return = p, p = c;
          }
          return o(p);
        case Lt:
          return A = m._init, Q(p, c, A(m._payload), _);
      }
      if (yr(m)) return E(p, c, m, _);
      if (sr(m)) return z(p, c, m, _);
      zl(p, m);
    }
    return typeof m == "string" && m !== "" || typeof m == "number" ? (m = "" + m, c !== null && c.tag === 6 ? (n(p, c.sibling), c = l(c, m), c.return = p, p = c) : (n(p, c), c = co(m, p.mode, _), c.return = p, p = c), o(p)) : n(p, c);
  }
  return Q;
}
var Vn = bc(!0), Gc = bc(!1), ai = Xt(null), ui = null, Ln = null, As = null;
function Ls() {
  As = Ln = ui = null;
}
function Ws(e) {
  var t = ai.current;
  J(ai), e._currentValue = t;
}
function Ho(e, t, n) {
  for (; e !== null; ) {
    var r = e.alternate;
    if ((e.childLanes & t) !== t ? (e.childLanes |= t, r !== null && (r.childLanes |= t)) : r !== null && (r.childLanes & t) !== t && (r.childLanes |= t), e === n) break;
    e = e.return;
  }
}
function Rn(e, t) {
  ui = e, As = Ln = null, e = e.dependencies, e !== null && e.firstContext !== null && (e.lanes & t && (We = !0), e.firstContext = null);
}
function Xe(e) {
  var t = e._currentValue;
  if (As !== e) if (e = { context: e, memoizedValue: t, next: null }, Ln === null) {
    if (ui === null) throw Error(S(308));
    Ln = e, ui.dependencies = { lanes: 0, firstContext: e };
  } else Ln = Ln.next = e;
  return t;
}
var ln = null;
function Fs(e) {
  ln === null ? ln = [e] : ln.push(e);
}
function Xc(e, t, n, r) {
  var l = t.interleaved;
  return l === null ? (n.next = n, Fs(t)) : (n.next = l.next, l.next = n), t.interleaved = n, wt(e, r);
}
function wt(e, t) {
  e.lanes |= t;
  var n = e.alternate;
  for (n !== null && (n.lanes |= t), n = e, e = e.return; e !== null; ) e.childLanes |= t, n = e.alternate, n !== null && (n.childLanes |= t), n = e, e = e.return;
  return n.tag === 3 ? n.stateNode : null;
}
var Wt = !1;
function Ds(e) {
  e.updateQueue = { baseState: e.memoizedState, firstBaseUpdate: null, lastBaseUpdate: null, shared: { pending: null, interleaved: null, lanes: 0 }, effects: null };
}
function Yc(e, t) {
  e = e.updateQueue, t.updateQueue === e && (t.updateQueue = { baseState: e.baseState, firstBaseUpdate: e.firstBaseUpdate, lastBaseUpdate: e.lastBaseUpdate, shared: e.shared, effects: e.effects });
}
function yt(e, t) {
  return { eventTime: e, lane: t, tag: 0, payload: null, callback: null, next: null };
}
function Ut(e, t, n) {
  var r = e.updateQueue;
  if (r === null) return null;
  if (r = r.shared, H & 2) {
    var l = r.pending;
    return l === null ? t.next = t : (t.next = l.next, l.next = t), r.pending = t, wt(e, n);
  }
  return l = r.interleaved, l === null ? (t.next = t, Fs(r)) : (t.next = l.next, l.next = t), r.interleaved = t, wt(e, n);
}
function Bl(e, t, n) {
  if (t = t.updateQueue, t !== null && (t = t.shared, (n & 4194240) !== 0)) {
    var r = t.lanes;
    r &= e.pendingLanes, n |= r, t.lanes = n, xs(e, n);
  }
}
function lu(e, t) {
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
function ci(e, t, n, r) {
  var l = e.updateQueue;
  Wt = !1;
  var i = l.firstBaseUpdate, o = l.lastBaseUpdate, a = l.shared.pending;
  if (a !== null) {
    l.shared.pending = null;
    var u = a, d = u.next;
    u.next = null, o === null ? i = d : o.next = d, o = u;
    var y = e.alternate;
    y !== null && (y = y.updateQueue, a = y.lastBaseUpdate, a !== o && (a === null ? y.firstBaseUpdate = d : a.next = d, y.lastBaseUpdate = u));
  }
  if (i !== null) {
    var g = l.baseState;
    o = 0, y = d = u = null, a = i;
    do {
      var h = a.lane, k = a.eventTime;
      if ((r & h) === h) {
        y !== null && (y = y.next = {
          eventTime: k,
          lane: 0,
          tag: a.tag,
          payload: a.payload,
          callback: a.callback,
          next: null
        });
        e: {
          var E = e, z = a;
          switch (h = t, k = n, z.tag) {
            case 1:
              if (E = z.payload, typeof E == "function") {
                g = E.call(k, g, h);
                break e;
              }
              g = E;
              break e;
            case 3:
              E.flags = E.flags & -65537 | 128;
            case 0:
              if (E = z.payload, h = typeof E == "function" ? E.call(k, g, h) : E, h == null) break e;
              g = ie({}, g, h);
              break e;
            case 2:
              Wt = !0;
          }
        }
        a.callback !== null && a.lane !== 0 && (e.flags |= 64, h = l.effects, h === null ? l.effects = [a] : h.push(a));
      } else k = { eventTime: k, lane: h, tag: a.tag, payload: a.payload, callback: a.callback, next: null }, y === null ? (d = y = k, u = g) : y = y.next = k, o |= h;
      if (a = a.next, a === null) {
        if (a = l.shared.pending, a === null) break;
        h = a, a = h.next, h.next = null, l.lastBaseUpdate = h, l.shared.pending = null;
      }
    } while (!0);
    if (y === null && (u = g), l.baseState = u, l.firstBaseUpdate = d, l.lastBaseUpdate = y, t = l.shared.interleaved, t !== null) {
      l = t;
      do
        o |= l.lane, l = l.next;
      while (l !== t);
    } else i === null && (l.shared.lanes = 0);
    fn |= o, e.lanes = o, e.memoizedState = g;
  }
}
function iu(e, t, n) {
  if (e = t.effects, t.effects = null, e !== null) for (t = 0; t < e.length; t++) {
    var r = e[t], l = r.callback;
    if (l !== null) {
      if (r.callback = null, r = n, typeof l != "function") throw Error(S(191, l));
      l.call(r);
    }
  }
}
var el = {}, ct = Xt(el), Kr = Xt(el), Vr = Xt(el);
function on(e) {
  if (e === el) throw Error(S(174));
  return e;
}
function Os(e, t) {
  switch (Y(Vr, t), Y(Kr, e), Y(ct, el), e = t.nodeType, e) {
    case 9:
    case 11:
      t = (t = t.documentElement) ? t.namespaceURI : Eo(null, "");
      break;
    default:
      e = e === 8 ? t.parentNode : t, t = e.namespaceURI || null, e = e.tagName, t = Eo(t, e);
  }
  J(ct), Y(ct, t);
}
function Hn() {
  J(ct), J(Kr), J(Vr);
}
function Zc(e) {
  on(Vr.current);
  var t = on(ct.current), n = Eo(t, e.type);
  t !== n && (Y(Kr, e), Y(ct, n));
}
function Is(e) {
  Kr.current === e && (J(ct), J(Kr));
}
var re = Xt(0);
function di(e) {
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
var lo = [];
function Ms() {
  for (var e = 0; e < lo.length; e++) lo[e]._workInProgressVersionPrimary = null;
  lo.length = 0;
}
var $l = St.ReactCurrentDispatcher, io = St.ReactCurrentBatchConfig, dn = 0, le = null, fe = null, me = null, fi = !1, zr = !1, Hr = 0, Em = 0;
function _e() {
  throw Error(S(321));
}
function Rs(e, t) {
  if (t === null) return !1;
  for (var n = 0; n < t.length && n < e.length; n++) if (!lt(e[n], t[n])) return !1;
  return !0;
}
function Bs(e, t, n, r, l, i) {
  if (dn = i, le = t, t.memoizedState = null, t.updateQueue = null, t.lanes = 0, $l.current = e === null || e.memoizedState === null ? Cm : Pm, e = n(r, l), zr) {
    i = 0;
    do {
      if (zr = !1, Hr = 0, 25 <= i) throw Error(S(301));
      i += 1, me = fe = null, t.updateQueue = null, $l.current = Am, e = n(r, l);
    } while (zr);
  }
  if ($l.current = pi, t = fe !== null && fe.next !== null, dn = 0, me = fe = le = null, fi = !1, t) throw Error(S(300));
  return e;
}
function $s() {
  var e = Hr !== 0;
  return Hr = 0, e;
}
function st() {
  var e = { memoizedState: null, baseState: null, baseQueue: null, queue: null, next: null };
  return me === null ? le.memoizedState = me = e : me = me.next = e, me;
}
function Ye() {
  if (fe === null) {
    var e = le.alternate;
    e = e !== null ? e.memoizedState : null;
  } else e = fe.next;
  var t = me === null ? le.memoizedState : me.next;
  if (t !== null) me = t, fe = e;
  else {
    if (e === null) throw Error(S(310));
    fe = e, e = { memoizedState: fe.memoizedState, baseState: fe.baseState, baseQueue: fe.baseQueue, queue: fe.queue, next: null }, me === null ? le.memoizedState = me = e : me = me.next = e;
  }
  return me;
}
function Qr(e, t) {
  return typeof t == "function" ? t(e) : t;
}
function oo(e) {
  var t = Ye(), n = t.queue;
  if (n === null) throw Error(S(311));
  n.lastRenderedReducer = e;
  var r = fe, l = r.baseQueue, i = n.pending;
  if (i !== null) {
    if (l !== null) {
      var o = l.next;
      l.next = i.next, i.next = o;
    }
    r.baseQueue = l = i, n.pending = null;
  }
  if (l !== null) {
    i = l.next, r = r.baseState;
    var a = o = null, u = null, d = i;
    do {
      var y = d.lane;
      if ((dn & y) === y) u !== null && (u = u.next = { lane: 0, action: d.action, hasEagerState: d.hasEagerState, eagerState: d.eagerState, next: null }), r = d.hasEagerState ? d.eagerState : e(r, d.action);
      else {
        var g = {
          lane: y,
          action: d.action,
          hasEagerState: d.hasEagerState,
          eagerState: d.eagerState,
          next: null
        };
        u === null ? (a = u = g, o = r) : u = u.next = g, le.lanes |= y, fn |= y;
      }
      d = d.next;
    } while (d !== null && d !== i);
    u === null ? o = r : u.next = a, lt(r, t.memoizedState) || (We = !0), t.memoizedState = r, t.baseState = o, t.baseQueue = u, n.lastRenderedState = r;
  }
  if (e = n.interleaved, e !== null) {
    l = e;
    do
      i = l.lane, le.lanes |= i, fn |= i, l = l.next;
    while (l !== e);
  } else l === null && (n.lanes = 0);
  return [t.memoizedState, n.dispatch];
}
function so(e) {
  var t = Ye(), n = t.queue;
  if (n === null) throw Error(S(311));
  n.lastRenderedReducer = e;
  var r = n.dispatch, l = n.pending, i = t.memoizedState;
  if (l !== null) {
    n.pending = null;
    var o = l = l.next;
    do
      i = e(i, o.action), o = o.next;
    while (o !== l);
    lt(i, t.memoizedState) || (We = !0), t.memoizedState = i, t.baseQueue === null && (t.baseState = i), n.lastRenderedState = i;
  }
  return [i, r];
}
function Jc() {
}
function qc(e, t) {
  var n = le, r = Ye(), l = t(), i = !lt(r.memoizedState, l);
  if (i && (r.memoizedState = l, We = !0), r = r.queue, Us(nd.bind(null, n, r, e), [e]), r.getSnapshot !== t || i || me !== null && me.memoizedState.tag & 1) {
    if (n.flags |= 2048, br(9, td.bind(null, n, r, l, t), void 0, null), ge === null) throw Error(S(349));
    dn & 30 || ed(n, t, l);
  }
  return l;
}
function ed(e, t, n) {
  e.flags |= 16384, e = { getSnapshot: t, value: n }, t = le.updateQueue, t === null ? (t = { lastEffect: null, stores: null }, le.updateQueue = t, t.stores = [e]) : (n = t.stores, n === null ? t.stores = [e] : n.push(e));
}
function td(e, t, n, r) {
  t.value = n, t.getSnapshot = r, rd(t) && ld(e);
}
function nd(e, t, n) {
  return n(function() {
    rd(t) && ld(e);
  });
}
function rd(e) {
  var t = e.getSnapshot;
  e = e.value;
  try {
    var n = t();
    return !lt(e, n);
  } catch {
    return !0;
  }
}
function ld(e) {
  var t = wt(e, 1);
  t !== null && rt(t, e, 1, -1);
}
function ou(e) {
  var t = st();
  return typeof e == "function" && (e = e()), t.memoizedState = t.baseState = e, e = { pending: null, interleaved: null, lanes: 0, dispatch: null, lastRenderedReducer: Qr, lastRenderedState: e }, t.queue = e, e = e.dispatch = Nm.bind(null, le, e), [t.memoizedState, e];
}
function br(e, t, n, r) {
  return e = { tag: e, create: t, destroy: n, deps: r, next: null }, t = le.updateQueue, t === null ? (t = { lastEffect: null, stores: null }, le.updateQueue = t, t.lastEffect = e.next = e) : (n = t.lastEffect, n === null ? t.lastEffect = e.next = e : (r = n.next, n.next = e, e.next = r, t.lastEffect = e)), e;
}
function id() {
  return Ye().memoizedState;
}
function Ul(e, t, n, r) {
  var l = st();
  le.flags |= e, l.memoizedState = br(1 | t, n, void 0, r === void 0 ? null : r);
}
function Ti(e, t, n, r) {
  var l = Ye();
  r = r === void 0 ? null : r;
  var i = void 0;
  if (fe !== null) {
    var o = fe.memoizedState;
    if (i = o.destroy, r !== null && Rs(r, o.deps)) {
      l.memoizedState = br(t, n, i, r);
      return;
    }
  }
  le.flags |= e, l.memoizedState = br(1 | t, n, i, r);
}
function su(e, t) {
  return Ul(8390656, 8, e, t);
}
function Us(e, t) {
  return Ti(2048, 8, e, t);
}
function od(e, t) {
  return Ti(4, 2, e, t);
}
function sd(e, t) {
  return Ti(4, 4, e, t);
}
function ad(e, t) {
  if (typeof t == "function") return e = e(), t(e), function() {
    t(null);
  };
  if (t != null) return e = e(), t.current = e, function() {
    t.current = null;
  };
}
function ud(e, t, n) {
  return n = n != null ? n.concat([e]) : null, Ti(4, 4, ad.bind(null, t, e), n);
}
function Ks() {
}
function cd(e, t) {
  var n = Ye();
  t = t === void 0 ? null : t;
  var r = n.memoizedState;
  return r !== null && t !== null && Rs(t, r[1]) ? r[0] : (n.memoizedState = [e, t], e);
}
function dd(e, t) {
  var n = Ye();
  t = t === void 0 ? null : t;
  var r = n.memoizedState;
  return r !== null && t !== null && Rs(t, r[1]) ? r[0] : (e = e(), n.memoizedState = [e, t], e);
}
function fd(e, t, n) {
  return dn & 21 ? (lt(n, t) || (n = vc(), le.lanes |= n, fn |= n, e.baseState = !0), t) : (e.baseState && (e.baseState = !1, We = !0), e.memoizedState = n);
}
function Tm(e, t) {
  var n = X;
  X = n !== 0 && 4 > n ? n : 4, e(!0);
  var r = io.transition;
  io.transition = {};
  try {
    e(!1), t();
  } finally {
    X = n, io.transition = r;
  }
}
function pd() {
  return Ye().memoizedState;
}
function zm(e, t, n) {
  var r = Vt(e);
  if (n = { lane: r, action: n, hasEagerState: !1, eagerState: null, next: null }, md(e)) hd(t, n);
  else if (n = Xc(e, t, n, r), n !== null) {
    var l = ze();
    rt(n, e, r, l), gd(n, t, r);
  }
}
function Nm(e, t, n) {
  var r = Vt(e), l = { lane: r, action: n, hasEagerState: !1, eagerState: null, next: null };
  if (md(e)) hd(t, l);
  else {
    var i = e.alternate;
    if (e.lanes === 0 && (i === null || i.lanes === 0) && (i = t.lastRenderedReducer, i !== null)) try {
      var o = t.lastRenderedState, a = i(o, n);
      if (l.hasEagerState = !0, l.eagerState = a, lt(a, o)) {
        var u = t.interleaved;
        u === null ? (l.next = l, Fs(t)) : (l.next = u.next, u.next = l), t.interleaved = l;
        return;
      }
    } catch {
    } finally {
    }
    n = Xc(e, t, l, r), n !== null && (l = ze(), rt(n, e, r, l), gd(n, t, r));
  }
}
function md(e) {
  var t = e.alternate;
  return e === le || t !== null && t === le;
}
function hd(e, t) {
  zr = fi = !0;
  var n = e.pending;
  n === null ? t.next = t : (t.next = n.next, n.next = t), e.pending = t;
}
function gd(e, t, n) {
  if (n & 4194240) {
    var r = t.lanes;
    r &= e.pendingLanes, n |= r, t.lanes = n, xs(e, n);
  }
}
var pi = { readContext: Xe, useCallback: _e, useContext: _e, useEffect: _e, useImperativeHandle: _e, useInsertionEffect: _e, useLayoutEffect: _e, useMemo: _e, useReducer: _e, useRef: _e, useState: _e, useDebugValue: _e, useDeferredValue: _e, useTransition: _e, useMutableSource: _e, useSyncExternalStore: _e, useId: _e, unstable_isNewReconciler: !1 }, Cm = { readContext: Xe, useCallback: function(e, t) {
  return st().memoizedState = [e, t === void 0 ? null : t], e;
}, useContext: Xe, useEffect: su, useImperativeHandle: function(e, t, n) {
  return n = n != null ? n.concat([e]) : null, Ul(
    4194308,
    4,
    ad.bind(null, t, e),
    n
  );
}, useLayoutEffect: function(e, t) {
  return Ul(4194308, 4, e, t);
}, useInsertionEffect: function(e, t) {
  return Ul(4, 2, e, t);
}, useMemo: function(e, t) {
  var n = st();
  return t = t === void 0 ? null : t, e = e(), n.memoizedState = [e, t], e;
}, useReducer: function(e, t, n) {
  var r = st();
  return t = n !== void 0 ? n(t) : t, r.memoizedState = r.baseState = t, e = { pending: null, interleaved: null, lanes: 0, dispatch: null, lastRenderedReducer: e, lastRenderedState: t }, r.queue = e, e = e.dispatch = zm.bind(null, le, e), [r.memoizedState, e];
}, useRef: function(e) {
  var t = st();
  return e = { current: e }, t.memoizedState = e;
}, useState: ou, useDebugValue: Ks, useDeferredValue: function(e) {
  return st().memoizedState = e;
}, useTransition: function() {
  var e = ou(!1), t = e[0];
  return e = Tm.bind(null, e[1]), st().memoizedState = e, [t, e];
}, useMutableSource: function() {
}, useSyncExternalStore: function(e, t, n) {
  var r = le, l = st();
  if (q) {
    if (n === void 0) throw Error(S(407));
    n = n();
  } else {
    if (n = t(), ge === null) throw Error(S(349));
    dn & 30 || ed(r, t, n);
  }
  l.memoizedState = n;
  var i = { value: n, getSnapshot: t };
  return l.queue = i, su(nd.bind(
    null,
    r,
    i,
    e
  ), [e]), r.flags |= 2048, br(9, td.bind(null, r, i, n, t), void 0, null), n;
}, useId: function() {
  var e = st(), t = ge.identifierPrefix;
  if (q) {
    var n = vt, r = gt;
    n = (r & ~(1 << 32 - nt(r) - 1)).toString(32) + n, t = ":" + t + "R" + n, n = Hr++, 0 < n && (t += "H" + n.toString(32)), t += ":";
  } else n = Em++, t = ":" + t + "r" + n.toString(32) + ":";
  return e.memoizedState = t;
}, unstable_isNewReconciler: !1 }, Pm = {
  readContext: Xe,
  useCallback: cd,
  useContext: Xe,
  useEffect: Us,
  useImperativeHandle: ud,
  useInsertionEffect: od,
  useLayoutEffect: sd,
  useMemo: dd,
  useReducer: oo,
  useRef: id,
  useState: function() {
    return oo(Qr);
  },
  useDebugValue: Ks,
  useDeferredValue: function(e) {
    var t = Ye();
    return fd(t, fe.memoizedState, e);
  },
  useTransition: function() {
    var e = oo(Qr)[0], t = Ye().memoizedState;
    return [e, t];
  },
  useMutableSource: Jc,
  useSyncExternalStore: qc,
  useId: pd,
  unstable_isNewReconciler: !1
}, Am = { readContext: Xe, useCallback: cd, useContext: Xe, useEffect: Us, useImperativeHandle: ud, useInsertionEffect: od, useLayoutEffect: sd, useMemo: dd, useReducer: so, useRef: id, useState: function() {
  return so(Qr);
}, useDebugValue: Ks, useDeferredValue: function(e) {
  var t = Ye();
  return fe === null ? t.memoizedState = e : fd(t, fe.memoizedState, e);
}, useTransition: function() {
  var e = so(Qr)[0], t = Ye().memoizedState;
  return [e, t];
}, useMutableSource: Jc, useSyncExternalStore: qc, useId: pd, unstable_isNewReconciler: !1 };
function qe(e, t) {
  if (e && e.defaultProps) {
    t = ie({}, t), e = e.defaultProps;
    for (var n in e) t[n] === void 0 && (t[n] = e[n]);
    return t;
  }
  return t;
}
function Qo(e, t, n, r) {
  t = e.memoizedState, n = n(r, t), n = n == null ? t : ie({}, t, n), e.memoizedState = n, e.lanes === 0 && (e.updateQueue.baseState = n);
}
var zi = { isMounted: function(e) {
  return (e = e._reactInternals) ? hn(e) === e : !1;
}, enqueueSetState: function(e, t, n) {
  e = e._reactInternals;
  var r = ze(), l = Vt(e), i = yt(r, l);
  i.payload = t, n != null && (i.callback = n), t = Ut(e, i, l), t !== null && (rt(t, e, l, r), Bl(t, e, l));
}, enqueueReplaceState: function(e, t, n) {
  e = e._reactInternals;
  var r = ze(), l = Vt(e), i = yt(r, l);
  i.tag = 1, i.payload = t, n != null && (i.callback = n), t = Ut(e, i, l), t !== null && (rt(t, e, l, r), Bl(t, e, l));
}, enqueueForceUpdate: function(e, t) {
  e = e._reactInternals;
  var n = ze(), r = Vt(e), l = yt(n, r);
  l.tag = 2, t != null && (l.callback = t), t = Ut(e, l, r), t !== null && (rt(t, e, r, n), Bl(t, e, r));
} };
function au(e, t, n, r, l, i, o) {
  return e = e.stateNode, typeof e.shouldComponentUpdate == "function" ? e.shouldComponentUpdate(r, i, o) : t.prototype && t.prototype.isPureReactComponent ? !Rr(n, r) || !Rr(l, i) : !0;
}
function vd(e, t, n) {
  var r = !1, l = bt, i = t.contextType;
  return typeof i == "object" && i !== null ? i = Xe(i) : (l = De(t) ? un : Ee.current, r = t.contextTypes, i = (r = r != null) ? Un(e, l) : bt), t = new t(n, i), e.memoizedState = t.state !== null && t.state !== void 0 ? t.state : null, t.updater = zi, e.stateNode = t, t._reactInternals = e, r && (e = e.stateNode, e.__reactInternalMemoizedUnmaskedChildContext = l, e.__reactInternalMemoizedMaskedChildContext = i), t;
}
function uu(e, t, n, r) {
  e = t.state, typeof t.componentWillReceiveProps == "function" && t.componentWillReceiveProps(n, r), typeof t.UNSAFE_componentWillReceiveProps == "function" && t.UNSAFE_componentWillReceiveProps(n, r), t.state !== e && zi.enqueueReplaceState(t, t.state, null);
}
function bo(e, t, n, r) {
  var l = e.stateNode;
  l.props = n, l.state = e.memoizedState, l.refs = {}, Ds(e);
  var i = t.contextType;
  typeof i == "object" && i !== null ? l.context = Xe(i) : (i = De(t) ? un : Ee.current, l.context = Un(e, i)), l.state = e.memoizedState, i = t.getDerivedStateFromProps, typeof i == "function" && (Qo(e, t, i, n), l.state = e.memoizedState), typeof t.getDerivedStateFromProps == "function" || typeof l.getSnapshotBeforeUpdate == "function" || typeof l.UNSAFE_componentWillMount != "function" && typeof l.componentWillMount != "function" || (t = l.state, typeof l.componentWillMount == "function" && l.componentWillMount(), typeof l.UNSAFE_componentWillMount == "function" && l.UNSAFE_componentWillMount(), t !== l.state && zi.enqueueReplaceState(l, l.state, null), ci(e, n, l, r), l.state = e.memoizedState), typeof l.componentDidMount == "function" && (e.flags |= 4194308);
}
function Qn(e, t) {
  try {
    var n = "", r = t;
    do
      n += ip(r), r = r.return;
    while (r);
    var l = n;
  } catch (i) {
    l = `
Error generating stack: ` + i.message + `
` + i.stack;
  }
  return { value: e, source: t, stack: l, digest: null };
}
function ao(e, t, n) {
  return { value: e, source: null, stack: n ?? null, digest: t ?? null };
}
function Go(e, t) {
  try {
    console.error(t.value);
  } catch (n) {
    setTimeout(function() {
      throw n;
    });
  }
}
var Lm = typeof WeakMap == "function" ? WeakMap : Map;
function yd(e, t, n) {
  n = yt(-1, n), n.tag = 3, n.payload = { element: null };
  var r = t.value;
  return n.callback = function() {
    hi || (hi = !0, ls = r), Go(e, t);
  }, n;
}
function kd(e, t, n) {
  n = yt(-1, n), n.tag = 3;
  var r = e.type.getDerivedStateFromError;
  if (typeof r == "function") {
    var l = t.value;
    n.payload = function() {
      return r(l);
    }, n.callback = function() {
      Go(e, t);
    };
  }
  var i = e.stateNode;
  return i !== null && typeof i.componentDidCatch == "function" && (n.callback = function() {
    Go(e, t), typeof r != "function" && (Kt === null ? Kt = /* @__PURE__ */ new Set([this]) : Kt.add(this));
    var o = t.stack;
    this.componentDidCatch(t.value, { componentStack: o !== null ? o : "" });
  }), n;
}
function cu(e, t, n) {
  var r = e.pingCache;
  if (r === null) {
    r = e.pingCache = new Lm();
    var l = /* @__PURE__ */ new Set();
    r.set(t, l);
  } else l = r.get(t), l === void 0 && (l = /* @__PURE__ */ new Set(), r.set(t, l));
  l.has(n) || (l.add(n), e = Qm.bind(null, e, t, n), t.then(e, e));
}
function du(e) {
  do {
    var t;
    if ((t = e.tag === 13) && (t = e.memoizedState, t = t !== null ? t.dehydrated !== null : !0), t) return e;
    e = e.return;
  } while (e !== null);
  return null;
}
function fu(e, t, n, r, l) {
  return e.mode & 1 ? (e.flags |= 65536, e.lanes = l, e) : (e === t ? e.flags |= 65536 : (e.flags |= 128, n.flags |= 131072, n.flags &= -52805, n.tag === 1 && (n.alternate === null ? n.tag = 17 : (t = yt(-1, 1), t.tag = 2, Ut(n, t, 1))), n.lanes |= 1), e);
}
var Wm = St.ReactCurrentOwner, We = !1;
function Te(e, t, n, r) {
  t.child = e === null ? Gc(t, null, n, r) : Vn(t, e.child, n, r);
}
function pu(e, t, n, r, l) {
  n = n.render;
  var i = t.ref;
  return Rn(t, l), r = Bs(e, t, n, r, i, l), n = $s(), e !== null && !We ? (t.updateQueue = e.updateQueue, t.flags &= -2053, e.lanes &= ~l, _t(e, t, l)) : (q && n && Ns(t), t.flags |= 1, Te(e, t, r, l), t.child);
}
function mu(e, t, n, r, l) {
  if (e === null) {
    var i = n.type;
    return typeof i == "function" && !Zs(i) && i.defaultProps === void 0 && n.compare === null && n.defaultProps === void 0 ? (t.tag = 15, t.type = i, xd(e, t, i, r, l)) : (e = Ql(n.type, null, r, t, t.mode, l), e.ref = t.ref, e.return = t, t.child = e);
  }
  if (i = e.child, !(e.lanes & l)) {
    var o = i.memoizedProps;
    if (n = n.compare, n = n !== null ? n : Rr, n(o, r) && e.ref === t.ref) return _t(e, t, l);
  }
  return t.flags |= 1, e = Ht(i, r), e.ref = t.ref, e.return = t, t.child = e;
}
function xd(e, t, n, r, l) {
  if (e !== null) {
    var i = e.memoizedProps;
    if (Rr(i, r) && e.ref === t.ref) if (We = !1, t.pendingProps = r = i, (e.lanes & l) !== 0) e.flags & 131072 && (We = !0);
    else return t.lanes = e.lanes, _t(e, t, l);
  }
  return Xo(e, t, n, r, l);
}
function wd(e, t, n) {
  var r = t.pendingProps, l = r.children, i = e !== null ? e.memoizedState : null;
  if (r.mode === "hidden") if (!(t.mode & 1)) t.memoizedState = { baseLanes: 0, cachePool: null, transitions: null }, Y(Fn, Ie), Ie |= n;
  else {
    if (!(n & 1073741824)) return e = i !== null ? i.baseLanes | n : n, t.lanes = t.childLanes = 1073741824, t.memoizedState = { baseLanes: e, cachePool: null, transitions: null }, t.updateQueue = null, Y(Fn, Ie), Ie |= e, null;
    t.memoizedState = { baseLanes: 0, cachePool: null, transitions: null }, r = i !== null ? i.baseLanes : n, Y(Fn, Ie), Ie |= r;
  }
  else i !== null ? (r = i.baseLanes | n, t.memoizedState = null) : r = n, Y(Fn, Ie), Ie |= r;
  return Te(e, t, l, n), t.child;
}
function _d(e, t) {
  var n = t.ref;
  (e === null && n !== null || e !== null && e.ref !== n) && (t.flags |= 512, t.flags |= 2097152);
}
function Xo(e, t, n, r, l) {
  var i = De(n) ? un : Ee.current;
  return i = Un(t, i), Rn(t, l), n = Bs(e, t, n, r, i, l), r = $s(), e !== null && !We ? (t.updateQueue = e.updateQueue, t.flags &= -2053, e.lanes &= ~l, _t(e, t, l)) : (q && r && Ns(t), t.flags |= 1, Te(e, t, n, l), t.child);
}
function hu(e, t, n, r, l) {
  if (De(n)) {
    var i = !0;
    ii(t);
  } else i = !1;
  if (Rn(t, l), t.stateNode === null) Kl(e, t), vd(t, n, r), bo(t, n, r, l), r = !0;
  else if (e === null) {
    var o = t.stateNode, a = t.memoizedProps;
    o.props = a;
    var u = o.context, d = n.contextType;
    typeof d == "object" && d !== null ? d = Xe(d) : (d = De(n) ? un : Ee.current, d = Un(t, d));
    var y = n.getDerivedStateFromProps, g = typeof y == "function" || typeof o.getSnapshotBeforeUpdate == "function";
    g || typeof o.UNSAFE_componentWillReceiveProps != "function" && typeof o.componentWillReceiveProps != "function" || (a !== r || u !== d) && uu(t, o, r, d), Wt = !1;
    var h = t.memoizedState;
    o.state = h, ci(t, r, o, l), u = t.memoizedState, a !== r || h !== u || Fe.current || Wt ? (typeof y == "function" && (Qo(t, n, y, r), u = t.memoizedState), (a = Wt || au(t, n, a, r, h, u, d)) ? (g || typeof o.UNSAFE_componentWillMount != "function" && typeof o.componentWillMount != "function" || (typeof o.componentWillMount == "function" && o.componentWillMount(), typeof o.UNSAFE_componentWillMount == "function" && o.UNSAFE_componentWillMount()), typeof o.componentDidMount == "function" && (t.flags |= 4194308)) : (typeof o.componentDidMount == "function" && (t.flags |= 4194308), t.memoizedProps = r, t.memoizedState = u), o.props = r, o.state = u, o.context = d, r = a) : (typeof o.componentDidMount == "function" && (t.flags |= 4194308), r = !1);
  } else {
    o = t.stateNode, Yc(e, t), a = t.memoizedProps, d = t.type === t.elementType ? a : qe(t.type, a), o.props = d, g = t.pendingProps, h = o.context, u = n.contextType, typeof u == "object" && u !== null ? u = Xe(u) : (u = De(n) ? un : Ee.current, u = Un(t, u));
    var k = n.getDerivedStateFromProps;
    (y = typeof k == "function" || typeof o.getSnapshotBeforeUpdate == "function") || typeof o.UNSAFE_componentWillReceiveProps != "function" && typeof o.componentWillReceiveProps != "function" || (a !== g || h !== u) && uu(t, o, r, u), Wt = !1, h = t.memoizedState, o.state = h, ci(t, r, o, l);
    var E = t.memoizedState;
    a !== g || h !== E || Fe.current || Wt ? (typeof k == "function" && (Qo(t, n, k, r), E = t.memoizedState), (d = Wt || au(t, n, d, r, h, E, u) || !1) ? (y || typeof o.UNSAFE_componentWillUpdate != "function" && typeof o.componentWillUpdate != "function" || (typeof o.componentWillUpdate == "function" && o.componentWillUpdate(r, E, u), typeof o.UNSAFE_componentWillUpdate == "function" && o.UNSAFE_componentWillUpdate(r, E, u)), typeof o.componentDidUpdate == "function" && (t.flags |= 4), typeof o.getSnapshotBeforeUpdate == "function" && (t.flags |= 1024)) : (typeof o.componentDidUpdate != "function" || a === e.memoizedProps && h === e.memoizedState || (t.flags |= 4), typeof o.getSnapshotBeforeUpdate != "function" || a === e.memoizedProps && h === e.memoizedState || (t.flags |= 1024), t.memoizedProps = r, t.memoizedState = E), o.props = r, o.state = E, o.context = u, r = d) : (typeof o.componentDidUpdate != "function" || a === e.memoizedProps && h === e.memoizedState || (t.flags |= 4), typeof o.getSnapshotBeforeUpdate != "function" || a === e.memoizedProps && h === e.memoizedState || (t.flags |= 1024), r = !1);
  }
  return Yo(e, t, n, r, i, l);
}
function Yo(e, t, n, r, l, i) {
  _d(e, t);
  var o = (t.flags & 128) !== 0;
  if (!r && !o) return l && eu(t, n, !1), _t(e, t, i);
  r = t.stateNode, Wm.current = t;
  var a = o && typeof n.getDerivedStateFromError != "function" ? null : r.render();
  return t.flags |= 1, e !== null && o ? (t.child = Vn(t, e.child, null, i), t.child = Vn(t, null, a, i)) : Te(e, t, a, i), t.memoizedState = r.state, l && eu(t, n, !0), t.child;
}
function Sd(e) {
  var t = e.stateNode;
  t.pendingContext ? qa(e, t.pendingContext, t.pendingContext !== t.context) : t.context && qa(e, t.context, !1), Os(e, t.containerInfo);
}
function gu(e, t, n, r, l) {
  return Kn(), Ps(l), t.flags |= 256, Te(e, t, n, r), t.child;
}
var Zo = { dehydrated: null, treeContext: null, retryLane: 0 };
function Jo(e) {
  return { baseLanes: e, cachePool: null, transitions: null };
}
function jd(e, t, n) {
  var r = t.pendingProps, l = re.current, i = !1, o = (t.flags & 128) !== 0, a;
  if ((a = o) || (a = e !== null && e.memoizedState === null ? !1 : (l & 2) !== 0), a ? (i = !0, t.flags &= -129) : (e === null || e.memoizedState !== null) && (l |= 1), Y(re, l & 1), e === null)
    return Vo(t), e = t.memoizedState, e !== null && (e = e.dehydrated, e !== null) ? (t.mode & 1 ? e.data === "$!" ? t.lanes = 8 : t.lanes = 1073741824 : t.lanes = 1, null) : (o = r.children, e = r.fallback, i ? (r = t.mode, i = t.child, o = { mode: "hidden", children: o }, !(r & 1) && i !== null ? (i.childLanes = 0, i.pendingProps = o) : i = Pi(o, r, 0, null), e = an(e, r, n, null), i.return = t, e.return = t, i.sibling = e, t.child = i, t.child.memoizedState = Jo(n), t.memoizedState = Zo, e) : Vs(t, o));
  if (l = e.memoizedState, l !== null && (a = l.dehydrated, a !== null)) return Fm(e, t, o, r, a, l, n);
  if (i) {
    i = r.fallback, o = t.mode, l = e.child, a = l.sibling;
    var u = { mode: "hidden", children: r.children };
    return !(o & 1) && t.child !== l ? (r = t.child, r.childLanes = 0, r.pendingProps = u, t.deletions = null) : (r = Ht(l, u), r.subtreeFlags = l.subtreeFlags & 14680064), a !== null ? i = Ht(a, i) : (i = an(i, o, n, null), i.flags |= 2), i.return = t, r.return = t, r.sibling = i, t.child = r, r = i, i = t.child, o = e.child.memoizedState, o = o === null ? Jo(n) : { baseLanes: o.baseLanes | n, cachePool: null, transitions: o.transitions }, i.memoizedState = o, i.childLanes = e.childLanes & ~n, t.memoizedState = Zo, r;
  }
  return i = e.child, e = i.sibling, r = Ht(i, { mode: "visible", children: r.children }), !(t.mode & 1) && (r.lanes = n), r.return = t, r.sibling = null, e !== null && (n = t.deletions, n === null ? (t.deletions = [e], t.flags |= 16) : n.push(e)), t.child = r, t.memoizedState = null, r;
}
function Vs(e, t) {
  return t = Pi({ mode: "visible", children: t }, e.mode, 0, null), t.return = e, e.child = t;
}
function Nl(e, t, n, r) {
  return r !== null && Ps(r), Vn(t, e.child, null, n), e = Vs(t, t.pendingProps.children), e.flags |= 2, t.memoizedState = null, e;
}
function Fm(e, t, n, r, l, i, o) {
  if (n)
    return t.flags & 256 ? (t.flags &= -257, r = ao(Error(S(422))), Nl(e, t, o, r)) : t.memoizedState !== null ? (t.child = e.child, t.flags |= 128, null) : (i = r.fallback, l = t.mode, r = Pi({ mode: "visible", children: r.children }, l, 0, null), i = an(i, l, o, null), i.flags |= 2, r.return = t, i.return = t, r.sibling = i, t.child = r, t.mode & 1 && Vn(t, e.child, null, o), t.child.memoizedState = Jo(o), t.memoizedState = Zo, i);
  if (!(t.mode & 1)) return Nl(e, t, o, null);
  if (l.data === "$!") {
    if (r = l.nextSibling && l.nextSibling.dataset, r) var a = r.dgst;
    return r = a, i = Error(S(419)), r = ao(i, r, void 0), Nl(e, t, o, r);
  }
  if (a = (o & e.childLanes) !== 0, We || a) {
    if (r = ge, r !== null) {
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
      l = l & (r.suspendedLanes | o) ? 0 : l, l !== 0 && l !== i.retryLane && (i.retryLane = l, wt(e, l), rt(r, e, l, -1));
    }
    return Ys(), r = ao(Error(S(421))), Nl(e, t, o, r);
  }
  return l.data === "$?" ? (t.flags |= 128, t.child = e.child, t = bm.bind(null, e), l._reactRetry = t, null) : (e = i.treeContext, Me = $t(l.nextSibling), Re = t, q = !0, tt = null, e !== null && (He[Qe++] = gt, He[Qe++] = vt, He[Qe++] = cn, gt = e.id, vt = e.overflow, cn = t), t = Vs(t, r.children), t.flags |= 4096, t);
}
function vu(e, t, n) {
  e.lanes |= t;
  var r = e.alternate;
  r !== null && (r.lanes |= t), Ho(e.return, t, n);
}
function uo(e, t, n, r, l) {
  var i = e.memoizedState;
  i === null ? e.memoizedState = { isBackwards: t, rendering: null, renderingStartTime: 0, last: r, tail: n, tailMode: l } : (i.isBackwards = t, i.rendering = null, i.renderingStartTime = 0, i.last = r, i.tail = n, i.tailMode = l);
}
function Ed(e, t, n) {
  var r = t.pendingProps, l = r.revealOrder, i = r.tail;
  if (Te(e, t, r.children, n), r = re.current, r & 2) r = r & 1 | 2, t.flags |= 128;
  else {
    if (e !== null && e.flags & 128) e: for (e = t.child; e !== null; ) {
      if (e.tag === 13) e.memoizedState !== null && vu(e, n, t);
      else if (e.tag === 19) vu(e, n, t);
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
  if (Y(re, r), !(t.mode & 1)) t.memoizedState = null;
  else switch (l) {
    case "forwards":
      for (n = t.child, l = null; n !== null; ) e = n.alternate, e !== null && di(e) === null && (l = n), n = n.sibling;
      n = l, n === null ? (l = t.child, t.child = null) : (l = n.sibling, n.sibling = null), uo(t, !1, l, n, i);
      break;
    case "backwards":
      for (n = null, l = t.child, t.child = null; l !== null; ) {
        if (e = l.alternate, e !== null && di(e) === null) {
          t.child = l;
          break;
        }
        e = l.sibling, l.sibling = n, n = l, l = e;
      }
      uo(t, !0, n, null, i);
      break;
    case "together":
      uo(t, !1, null, null, void 0);
      break;
    default:
      t.memoizedState = null;
  }
  return t.child;
}
function Kl(e, t) {
  !(t.mode & 1) && e !== null && (e.alternate = null, t.alternate = null, t.flags |= 2);
}
function _t(e, t, n) {
  if (e !== null && (t.dependencies = e.dependencies), fn |= t.lanes, !(n & t.childLanes)) return null;
  if (e !== null && t.child !== e.child) throw Error(S(153));
  if (t.child !== null) {
    for (e = t.child, n = Ht(e, e.pendingProps), t.child = n, n.return = t; e.sibling !== null; ) e = e.sibling, n = n.sibling = Ht(e, e.pendingProps), n.return = t;
    n.sibling = null;
  }
  return t.child;
}
function Dm(e, t, n) {
  switch (t.tag) {
    case 3:
      Sd(t), Kn();
      break;
    case 5:
      Zc(t);
      break;
    case 1:
      De(t.type) && ii(t);
      break;
    case 4:
      Os(t, t.stateNode.containerInfo);
      break;
    case 10:
      var r = t.type._context, l = t.memoizedProps.value;
      Y(ai, r._currentValue), r._currentValue = l;
      break;
    case 13:
      if (r = t.memoizedState, r !== null)
        return r.dehydrated !== null ? (Y(re, re.current & 1), t.flags |= 128, null) : n & t.child.childLanes ? jd(e, t, n) : (Y(re, re.current & 1), e = _t(e, t, n), e !== null ? e.sibling : null);
      Y(re, re.current & 1);
      break;
    case 19:
      if (r = (n & t.childLanes) !== 0, e.flags & 128) {
        if (r) return Ed(e, t, n);
        t.flags |= 128;
      }
      if (l = t.memoizedState, l !== null && (l.rendering = null, l.tail = null, l.lastEffect = null), Y(re, re.current), r) break;
      return null;
    case 22:
    case 23:
      return t.lanes = 0, wd(e, t, n);
  }
  return _t(e, t, n);
}
var Td, qo, zd, Nd;
Td = function(e, t) {
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
qo = function() {
};
zd = function(e, t, n, r) {
  var l = e.memoizedProps;
  if (l !== r) {
    e = t.stateNode, on(ct.current);
    var i = null;
    switch (n) {
      case "input":
        l = wo(e, l), r = wo(e, r), i = [];
        break;
      case "select":
        l = ie({}, l, { value: void 0 }), r = ie({}, r, { value: void 0 }), i = [];
        break;
      case "textarea":
        l = jo(e, l), r = jo(e, r), i = [];
        break;
      default:
        typeof l.onClick != "function" && typeof r.onClick == "function" && (e.onclick = ri);
    }
    To(n, r);
    var o;
    n = null;
    for (d in l) if (!r.hasOwnProperty(d) && l.hasOwnProperty(d) && l[d] != null) if (d === "style") {
      var a = l[d];
      for (o in a) a.hasOwnProperty(o) && (n || (n = {}), n[o] = "");
    } else d !== "dangerouslySetInnerHTML" && d !== "children" && d !== "suppressContentEditableWarning" && d !== "suppressHydrationWarning" && d !== "autoFocus" && (Lr.hasOwnProperty(d) ? i || (i = []) : (i = i || []).push(d, null));
    for (d in r) {
      var u = r[d];
      if (a = l != null ? l[d] : void 0, r.hasOwnProperty(d) && u !== a && (u != null || a != null)) if (d === "style") if (a) {
        for (o in a) !a.hasOwnProperty(o) || u && u.hasOwnProperty(o) || (n || (n = {}), n[o] = "");
        for (o in u) u.hasOwnProperty(o) && a[o] !== u[o] && (n || (n = {}), n[o] = u[o]);
      } else n || (i || (i = []), i.push(
        d,
        n
      )), n = u;
      else d === "dangerouslySetInnerHTML" ? (u = u ? u.__html : void 0, a = a ? a.__html : void 0, u != null && a !== u && (i = i || []).push(d, u)) : d === "children" ? typeof u != "string" && typeof u != "number" || (i = i || []).push(d, "" + u) : d !== "suppressContentEditableWarning" && d !== "suppressHydrationWarning" && (Lr.hasOwnProperty(d) ? (u != null && d === "onScroll" && Z("scroll", e), i || a === u || (i = [])) : (i = i || []).push(d, u));
    }
    n && (i = i || []).push("style", n);
    var d = i;
    (t.updateQueue = d) && (t.flags |= 4);
  }
};
Nd = function(e, t, n, r) {
  n !== r && (t.flags |= 4);
};
function pr(e, t) {
  if (!q) switch (e.tailMode) {
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
function Se(e) {
  var t = e.alternate !== null && e.alternate.child === e.child, n = 0, r = 0;
  if (t) for (var l = e.child; l !== null; ) n |= l.lanes | l.childLanes, r |= l.subtreeFlags & 14680064, r |= l.flags & 14680064, l.return = e, l = l.sibling;
  else for (l = e.child; l !== null; ) n |= l.lanes | l.childLanes, r |= l.subtreeFlags, r |= l.flags, l.return = e, l = l.sibling;
  return e.subtreeFlags |= r, e.childLanes = n, t;
}
function Om(e, t, n) {
  var r = t.pendingProps;
  switch (Cs(t), t.tag) {
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
      return Se(t), null;
    case 1:
      return De(t.type) && li(), Se(t), null;
    case 3:
      return r = t.stateNode, Hn(), J(Fe), J(Ee), Ms(), r.pendingContext && (r.context = r.pendingContext, r.pendingContext = null), (e === null || e.child === null) && (Tl(t) ? t.flags |= 4 : e === null || e.memoizedState.isDehydrated && !(t.flags & 256) || (t.flags |= 1024, tt !== null && (ss(tt), tt = null))), qo(e, t), Se(t), null;
    case 5:
      Is(t);
      var l = on(Vr.current);
      if (n = t.type, e !== null && t.stateNode != null) zd(e, t, n, r, l), e.ref !== t.ref && (t.flags |= 512, t.flags |= 2097152);
      else {
        if (!r) {
          if (t.stateNode === null) throw Error(S(166));
          return Se(t), null;
        }
        if (e = on(ct.current), Tl(t)) {
          r = t.stateNode, n = t.type;
          var i = t.memoizedProps;
          switch (r[at] = t, r[Ur] = i, e = (t.mode & 1) !== 0, n) {
            case "dialog":
              Z("cancel", r), Z("close", r);
              break;
            case "iframe":
            case "object":
            case "embed":
              Z("load", r);
              break;
            case "video":
            case "audio":
              for (l = 0; l < xr.length; l++) Z(xr[l], r);
              break;
            case "source":
              Z("error", r);
              break;
            case "img":
            case "image":
            case "link":
              Z(
                "error",
                r
              ), Z("load", r);
              break;
            case "details":
              Z("toggle", r);
              break;
            case "input":
              Ta(r, i), Z("invalid", r);
              break;
            case "select":
              r._wrapperState = { wasMultiple: !!i.multiple }, Z("invalid", r);
              break;
            case "textarea":
              Na(r, i), Z("invalid", r);
          }
          To(n, i), l = null;
          for (var o in i) if (i.hasOwnProperty(o)) {
            var a = i[o];
            o === "children" ? typeof a == "string" ? r.textContent !== a && (i.suppressHydrationWarning !== !0 && El(r.textContent, a, e), l = ["children", a]) : typeof a == "number" && r.textContent !== "" + a && (i.suppressHydrationWarning !== !0 && El(
              r.textContent,
              a,
              e
            ), l = ["children", "" + a]) : Lr.hasOwnProperty(o) && a != null && o === "onScroll" && Z("scroll", r);
          }
          switch (n) {
            case "input":
              vl(r), za(r, i, !0);
              break;
            case "textarea":
              vl(r), Ca(r);
              break;
            case "select":
            case "option":
              break;
            default:
              typeof i.onClick == "function" && (r.onclick = ri);
          }
          r = l, t.updateQueue = r, r !== null && (t.flags |= 4);
        } else {
          o = l.nodeType === 9 ? l : l.ownerDocument, e === "http://www.w3.org/1999/xhtml" && (e = nc(n)), e === "http://www.w3.org/1999/xhtml" ? n === "script" ? (e = o.createElement("div"), e.innerHTML = "<script><\/script>", e = e.removeChild(e.firstChild)) : typeof r.is == "string" ? e = o.createElement(n, { is: r.is }) : (e = o.createElement(n), n === "select" && (o = e, r.multiple ? o.multiple = !0 : r.size && (o.size = r.size))) : e = o.createElementNS(e, n), e[at] = t, e[Ur] = r, Td(e, t, !1, !1), t.stateNode = e;
          e: {
            switch (o = zo(n, r), n) {
              case "dialog":
                Z("cancel", e), Z("close", e), l = r;
                break;
              case "iframe":
              case "object":
              case "embed":
                Z("load", e), l = r;
                break;
              case "video":
              case "audio":
                for (l = 0; l < xr.length; l++) Z(xr[l], e);
                l = r;
                break;
              case "source":
                Z("error", e), l = r;
                break;
              case "img":
              case "image":
              case "link":
                Z(
                  "error",
                  e
                ), Z("load", e), l = r;
                break;
              case "details":
                Z("toggle", e), l = r;
                break;
              case "input":
                Ta(e, r), l = wo(e, r), Z("invalid", e);
                break;
              case "option":
                l = r;
                break;
              case "select":
                e._wrapperState = { wasMultiple: !!r.multiple }, l = ie({}, r, { value: void 0 }), Z("invalid", e);
                break;
              case "textarea":
                Na(e, r), l = jo(e, r), Z("invalid", e);
                break;
              default:
                l = r;
            }
            To(n, l), a = l;
            for (i in a) if (a.hasOwnProperty(i)) {
              var u = a[i];
              i === "style" ? ic(e, u) : i === "dangerouslySetInnerHTML" ? (u = u ? u.__html : void 0, u != null && rc(e, u)) : i === "children" ? typeof u == "string" ? (n !== "textarea" || u !== "") && Wr(e, u) : typeof u == "number" && Wr(e, "" + u) : i !== "suppressContentEditableWarning" && i !== "suppressHydrationWarning" && i !== "autoFocus" && (Lr.hasOwnProperty(i) ? u != null && i === "onScroll" && Z("scroll", e) : u != null && ms(e, i, u, o));
            }
            switch (n) {
              case "input":
                vl(e), za(e, r, !1);
                break;
              case "textarea":
                vl(e), Ca(e);
                break;
              case "option":
                r.value != null && e.setAttribute("value", "" + Qt(r.value));
                break;
              case "select":
                e.multiple = !!r.multiple, i = r.value, i != null ? Dn(e, !!r.multiple, i, !1) : r.defaultValue != null && Dn(
                  e,
                  !!r.multiple,
                  r.defaultValue,
                  !0
                );
                break;
              default:
                typeof l.onClick == "function" && (e.onclick = ri);
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
      return Se(t), null;
    case 6:
      if (e && t.stateNode != null) Nd(e, t, e.memoizedProps, r);
      else {
        if (typeof r != "string" && t.stateNode === null) throw Error(S(166));
        if (n = on(Vr.current), on(ct.current), Tl(t)) {
          if (r = t.stateNode, n = t.memoizedProps, r[at] = t, (i = r.nodeValue !== n) && (e = Re, e !== null)) switch (e.tag) {
            case 3:
              El(r.nodeValue, n, (e.mode & 1) !== 0);
              break;
            case 5:
              e.memoizedProps.suppressHydrationWarning !== !0 && El(r.nodeValue, n, (e.mode & 1) !== 0);
          }
          i && (t.flags |= 4);
        } else r = (n.nodeType === 9 ? n : n.ownerDocument).createTextNode(r), r[at] = t, t.stateNode = r;
      }
      return Se(t), null;
    case 13:
      if (J(re), r = t.memoizedState, e === null || e.memoizedState !== null && e.memoizedState.dehydrated !== null) {
        if (q && Me !== null && t.mode & 1 && !(t.flags & 128)) Qc(), Kn(), t.flags |= 98560, i = !1;
        else if (i = Tl(t), r !== null && r.dehydrated !== null) {
          if (e === null) {
            if (!i) throw Error(S(318));
            if (i = t.memoizedState, i = i !== null ? i.dehydrated : null, !i) throw Error(S(317));
            i[at] = t;
          } else Kn(), !(t.flags & 128) && (t.memoizedState = null), t.flags |= 4;
          Se(t), i = !1;
        } else tt !== null && (ss(tt), tt = null), i = !0;
        if (!i) return t.flags & 65536 ? t : null;
      }
      return t.flags & 128 ? (t.lanes = n, t) : (r = r !== null, r !== (e !== null && e.memoizedState !== null) && r && (t.child.flags |= 8192, t.mode & 1 && (e === null || re.current & 1 ? pe === 0 && (pe = 3) : Ys())), t.updateQueue !== null && (t.flags |= 4), Se(t), null);
    case 4:
      return Hn(), qo(e, t), e === null && Br(t.stateNode.containerInfo), Se(t), null;
    case 10:
      return Ws(t.type._context), Se(t), null;
    case 17:
      return De(t.type) && li(), Se(t), null;
    case 19:
      if (J(re), i = t.memoizedState, i === null) return Se(t), null;
      if (r = (t.flags & 128) !== 0, o = i.rendering, o === null) if (r) pr(i, !1);
      else {
        if (pe !== 0 || e !== null && e.flags & 128) for (e = t.child; e !== null; ) {
          if (o = di(e), o !== null) {
            for (t.flags |= 128, pr(i, !1), r = o.updateQueue, r !== null && (t.updateQueue = r, t.flags |= 4), t.subtreeFlags = 0, r = n, n = t.child; n !== null; ) i = n, e = r, i.flags &= 14680066, o = i.alternate, o === null ? (i.childLanes = 0, i.lanes = e, i.child = null, i.subtreeFlags = 0, i.memoizedProps = null, i.memoizedState = null, i.updateQueue = null, i.dependencies = null, i.stateNode = null) : (i.childLanes = o.childLanes, i.lanes = o.lanes, i.child = o.child, i.subtreeFlags = 0, i.deletions = null, i.memoizedProps = o.memoizedProps, i.memoizedState = o.memoizedState, i.updateQueue = o.updateQueue, i.type = o.type, e = o.dependencies, i.dependencies = e === null ? null : { lanes: e.lanes, firstContext: e.firstContext }), n = n.sibling;
            return Y(re, re.current & 1 | 2), t.child;
          }
          e = e.sibling;
        }
        i.tail !== null && ue() > bn && (t.flags |= 128, r = !0, pr(i, !1), t.lanes = 4194304);
      }
      else {
        if (!r) if (e = di(o), e !== null) {
          if (t.flags |= 128, r = !0, n = e.updateQueue, n !== null && (t.updateQueue = n, t.flags |= 4), pr(i, !0), i.tail === null && i.tailMode === "hidden" && !o.alternate && !q) return Se(t), null;
        } else 2 * ue() - i.renderingStartTime > bn && n !== 1073741824 && (t.flags |= 128, r = !0, pr(i, !1), t.lanes = 4194304);
        i.isBackwards ? (o.sibling = t.child, t.child = o) : (n = i.last, n !== null ? n.sibling = o : t.child = o, i.last = o);
      }
      return i.tail !== null ? (t = i.tail, i.rendering = t, i.tail = t.sibling, i.renderingStartTime = ue(), t.sibling = null, n = re.current, Y(re, r ? n & 1 | 2 : n & 1), t) : (Se(t), null);
    case 22:
    case 23:
      return Xs(), r = t.memoizedState !== null, e !== null && e.memoizedState !== null !== r && (t.flags |= 8192), r && t.mode & 1 ? Ie & 1073741824 && (Se(t), t.subtreeFlags & 6 && (t.flags |= 8192)) : Se(t), null;
    case 24:
      return null;
    case 25:
      return null;
  }
  throw Error(S(156, t.tag));
}
function Im(e, t) {
  switch (Cs(t), t.tag) {
    case 1:
      return De(t.type) && li(), e = t.flags, e & 65536 ? (t.flags = e & -65537 | 128, t) : null;
    case 3:
      return Hn(), J(Fe), J(Ee), Ms(), e = t.flags, e & 65536 && !(e & 128) ? (t.flags = e & -65537 | 128, t) : null;
    case 5:
      return Is(t), null;
    case 13:
      if (J(re), e = t.memoizedState, e !== null && e.dehydrated !== null) {
        if (t.alternate === null) throw Error(S(340));
        Kn();
      }
      return e = t.flags, e & 65536 ? (t.flags = e & -65537 | 128, t) : null;
    case 19:
      return J(re), null;
    case 4:
      return Hn(), null;
    case 10:
      return Ws(t.type._context), null;
    case 22:
    case 23:
      return Xs(), null;
    case 24:
      return null;
    default:
      return null;
  }
}
var Cl = !1, je = !1, Mm = typeof WeakSet == "function" ? WeakSet : Set, C = null;
function Wn(e, t) {
  var n = e.ref;
  if (n !== null) if (typeof n == "function") try {
    n(null);
  } catch (r) {
    ae(e, t, r);
  }
  else n.current = null;
}
function es(e, t, n) {
  try {
    n();
  } catch (r) {
    ae(e, t, r);
  }
}
var yu = !1;
function Rm(e, t) {
  if (Io = ei, e = Wc(), zs(e)) {
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
        var o = 0, a = -1, u = -1, d = 0, y = 0, g = e, h = null;
        t: for (; ; ) {
          for (var k; g !== n || l !== 0 && g.nodeType !== 3 || (a = o + l), g !== i || r !== 0 && g.nodeType !== 3 || (u = o + r), g.nodeType === 3 && (o += g.nodeValue.length), (k = g.firstChild) !== null; )
            h = g, g = k;
          for (; ; ) {
            if (g === e) break t;
            if (h === n && ++d === l && (a = o), h === i && ++y === r && (u = o), (k = g.nextSibling) !== null) break;
            g = h, h = g.parentNode;
          }
          g = k;
        }
        n = a === -1 || u === -1 ? null : { start: a, end: u };
      } else n = null;
    }
    n = n || { start: 0, end: 0 };
  } else n = null;
  for (Mo = { focusedElem: e, selectionRange: n }, ei = !1, C = t; C !== null; ) if (t = C, e = t.child, (t.subtreeFlags & 1028) !== 0 && e !== null) e.return = t, C = e;
  else for (; C !== null; ) {
    t = C;
    try {
      var E = t.alternate;
      if (t.flags & 1024) switch (t.tag) {
        case 0:
        case 11:
        case 15:
          break;
        case 1:
          if (E !== null) {
            var z = E.memoizedProps, Q = E.memoizedState, p = t.stateNode, c = p.getSnapshotBeforeUpdate(t.elementType === t.type ? z : qe(t.type, z), Q);
            p.__reactInternalSnapshotBeforeUpdate = c;
          }
          break;
        case 3:
          var m = t.stateNode.containerInfo;
          m.nodeType === 1 ? m.textContent = "" : m.nodeType === 9 && m.documentElement && m.removeChild(m.documentElement);
          break;
        case 5:
        case 6:
        case 4:
        case 17:
          break;
        default:
          throw Error(S(163));
      }
    } catch (_) {
      ae(t, t.return, _);
    }
    if (e = t.sibling, e !== null) {
      e.return = t.return, C = e;
      break;
    }
    C = t.return;
  }
  return E = yu, yu = !1, E;
}
function Nr(e, t, n) {
  var r = t.updateQueue;
  if (r = r !== null ? r.lastEffect : null, r !== null) {
    var l = r = r.next;
    do {
      if ((l.tag & e) === e) {
        var i = l.destroy;
        l.destroy = void 0, i !== void 0 && es(t, n, i);
      }
      l = l.next;
    } while (l !== r);
  }
}
function Ni(e, t) {
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
function ts(e) {
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
function Cd(e) {
  var t = e.alternate;
  t !== null && (e.alternate = null, Cd(t)), e.child = null, e.deletions = null, e.sibling = null, e.tag === 5 && (t = e.stateNode, t !== null && (delete t[at], delete t[Ur], delete t[$o], delete t[wm], delete t[_m])), e.stateNode = null, e.return = null, e.dependencies = null, e.memoizedProps = null, e.memoizedState = null, e.pendingProps = null, e.stateNode = null, e.updateQueue = null;
}
function Pd(e) {
  return e.tag === 5 || e.tag === 3 || e.tag === 4;
}
function ku(e) {
  e: for (; ; ) {
    for (; e.sibling === null; ) {
      if (e.return === null || Pd(e.return)) return null;
      e = e.return;
    }
    for (e.sibling.return = e.return, e = e.sibling; e.tag !== 5 && e.tag !== 6 && e.tag !== 18; ) {
      if (e.flags & 2 || e.child === null || e.tag === 4) continue e;
      e.child.return = e, e = e.child;
    }
    if (!(e.flags & 2)) return e.stateNode;
  }
}
function ns(e, t, n) {
  var r = e.tag;
  if (r === 5 || r === 6) e = e.stateNode, t ? n.nodeType === 8 ? n.parentNode.insertBefore(e, t) : n.insertBefore(e, t) : (n.nodeType === 8 ? (t = n.parentNode, t.insertBefore(e, n)) : (t = n, t.appendChild(e)), n = n._reactRootContainer, n != null || t.onclick !== null || (t.onclick = ri));
  else if (r !== 4 && (e = e.child, e !== null)) for (ns(e, t, n), e = e.sibling; e !== null; ) ns(e, t, n), e = e.sibling;
}
function rs(e, t, n) {
  var r = e.tag;
  if (r === 5 || r === 6) e = e.stateNode, t ? n.insertBefore(e, t) : n.appendChild(e);
  else if (r !== 4 && (e = e.child, e !== null)) for (rs(e, t, n), e = e.sibling; e !== null; ) rs(e, t, n), e = e.sibling;
}
var ye = null, et = !1;
function Pt(e, t, n) {
  for (n = n.child; n !== null; ) Ad(e, t, n), n = n.sibling;
}
function Ad(e, t, n) {
  if (ut && typeof ut.onCommitFiberUnmount == "function") try {
    ut.onCommitFiberUnmount(xi, n);
  } catch {
  }
  switch (n.tag) {
    case 5:
      je || Wn(n, t);
    case 6:
      var r = ye, l = et;
      ye = null, Pt(e, t, n), ye = r, et = l, ye !== null && (et ? (e = ye, n = n.stateNode, e.nodeType === 8 ? e.parentNode.removeChild(n) : e.removeChild(n)) : ye.removeChild(n.stateNode));
      break;
    case 18:
      ye !== null && (et ? (e = ye, n = n.stateNode, e.nodeType === 8 ? no(e.parentNode, n) : e.nodeType === 1 && no(e, n), Ir(e)) : no(ye, n.stateNode));
      break;
    case 4:
      r = ye, l = et, ye = n.stateNode.containerInfo, et = !0, Pt(e, t, n), ye = r, et = l;
      break;
    case 0:
    case 11:
    case 14:
    case 15:
      if (!je && (r = n.updateQueue, r !== null && (r = r.lastEffect, r !== null))) {
        l = r = r.next;
        do {
          var i = l, o = i.destroy;
          i = i.tag, o !== void 0 && (i & 2 || i & 4) && es(n, t, o), l = l.next;
        } while (l !== r);
      }
      Pt(e, t, n);
      break;
    case 1:
      if (!je && (Wn(n, t), r = n.stateNode, typeof r.componentWillUnmount == "function")) try {
        r.props = n.memoizedProps, r.state = n.memoizedState, r.componentWillUnmount();
      } catch (a) {
        ae(n, t, a);
      }
      Pt(e, t, n);
      break;
    case 21:
      Pt(e, t, n);
      break;
    case 22:
      n.mode & 1 ? (je = (r = je) || n.memoizedState !== null, Pt(e, t, n), je = r) : Pt(e, t, n);
      break;
    default:
      Pt(e, t, n);
  }
}
function xu(e) {
  var t = e.updateQueue;
  if (t !== null) {
    e.updateQueue = null;
    var n = e.stateNode;
    n === null && (n = e.stateNode = new Mm()), t.forEach(function(r) {
      var l = Gm.bind(null, e, r);
      n.has(r) || (n.add(r), r.then(l, l));
    });
  }
}
function Je(e, t) {
  var n = t.deletions;
  if (n !== null) for (var r = 0; r < n.length; r++) {
    var l = n[r];
    try {
      var i = e, o = t, a = o;
      e: for (; a !== null; ) {
        switch (a.tag) {
          case 5:
            ye = a.stateNode, et = !1;
            break e;
          case 3:
            ye = a.stateNode.containerInfo, et = !0;
            break e;
          case 4:
            ye = a.stateNode.containerInfo, et = !0;
            break e;
        }
        a = a.return;
      }
      if (ye === null) throw Error(S(160));
      Ad(i, o, l), ye = null, et = !1;
      var u = l.alternate;
      u !== null && (u.return = null), l.return = null;
    } catch (d) {
      ae(l, t, d);
    }
  }
  if (t.subtreeFlags & 12854) for (t = t.child; t !== null; ) Ld(t, e), t = t.sibling;
}
function Ld(e, t) {
  var n = e.alternate, r = e.flags;
  switch (e.tag) {
    case 0:
    case 11:
    case 14:
    case 15:
      if (Je(t, e), ot(e), r & 4) {
        try {
          Nr(3, e, e.return), Ni(3, e);
        } catch (z) {
          ae(e, e.return, z);
        }
        try {
          Nr(5, e, e.return);
        } catch (z) {
          ae(e, e.return, z);
        }
      }
      break;
    case 1:
      Je(t, e), ot(e), r & 512 && n !== null && Wn(n, n.return);
      break;
    case 5:
      if (Je(t, e), ot(e), r & 512 && n !== null && Wn(n, n.return), e.flags & 32) {
        var l = e.stateNode;
        try {
          Wr(l, "");
        } catch (z) {
          ae(e, e.return, z);
        }
      }
      if (r & 4 && (l = e.stateNode, l != null)) {
        var i = e.memoizedProps, o = n !== null ? n.memoizedProps : i, a = e.type, u = e.updateQueue;
        if (e.updateQueue = null, u !== null) try {
          a === "input" && i.type === "radio" && i.name != null && ec(l, i), zo(a, o);
          var d = zo(a, i);
          for (o = 0; o < u.length; o += 2) {
            var y = u[o], g = u[o + 1];
            y === "style" ? ic(l, g) : y === "dangerouslySetInnerHTML" ? rc(l, g) : y === "children" ? Wr(l, g) : ms(l, y, g, d);
          }
          switch (a) {
            case "input":
              _o(l, i);
              break;
            case "textarea":
              tc(l, i);
              break;
            case "select":
              var h = l._wrapperState.wasMultiple;
              l._wrapperState.wasMultiple = !!i.multiple;
              var k = i.value;
              k != null ? Dn(l, !!i.multiple, k, !1) : h !== !!i.multiple && (i.defaultValue != null ? Dn(
                l,
                !!i.multiple,
                i.defaultValue,
                !0
              ) : Dn(l, !!i.multiple, i.multiple ? [] : "", !1));
          }
          l[Ur] = i;
        } catch (z) {
          ae(e, e.return, z);
        }
      }
      break;
    case 6:
      if (Je(t, e), ot(e), r & 4) {
        if (e.stateNode === null) throw Error(S(162));
        l = e.stateNode, i = e.memoizedProps;
        try {
          l.nodeValue = i;
        } catch (z) {
          ae(e, e.return, z);
        }
      }
      break;
    case 3:
      if (Je(t, e), ot(e), r & 4 && n !== null && n.memoizedState.isDehydrated) try {
        Ir(t.containerInfo);
      } catch (z) {
        ae(e, e.return, z);
      }
      break;
    case 4:
      Je(t, e), ot(e);
      break;
    case 13:
      Je(t, e), ot(e), l = e.child, l.flags & 8192 && (i = l.memoizedState !== null, l.stateNode.isHidden = i, !i || l.alternate !== null && l.alternate.memoizedState !== null || (bs = ue())), r & 4 && xu(e);
      break;
    case 22:
      if (y = n !== null && n.memoizedState !== null, e.mode & 1 ? (je = (d = je) || y, Je(t, e), je = d) : Je(t, e), ot(e), r & 8192) {
        if (d = e.memoizedState !== null, (e.stateNode.isHidden = d) && !y && e.mode & 1) for (C = e, y = e.child; y !== null; ) {
          for (g = C = y; C !== null; ) {
            switch (h = C, k = h.child, h.tag) {
              case 0:
              case 11:
              case 14:
              case 15:
                Nr(4, h, h.return);
                break;
              case 1:
                Wn(h, h.return);
                var E = h.stateNode;
                if (typeof E.componentWillUnmount == "function") {
                  r = h, n = h.return;
                  try {
                    t = r, E.props = t.memoizedProps, E.state = t.memoizedState, E.componentWillUnmount();
                  } catch (z) {
                    ae(r, n, z);
                  }
                }
                break;
              case 5:
                Wn(h, h.return);
                break;
              case 22:
                if (h.memoizedState !== null) {
                  _u(g);
                  continue;
                }
            }
            k !== null ? (k.return = h, C = k) : _u(g);
          }
          y = y.sibling;
        }
        e: for (y = null, g = e; ; ) {
          if (g.tag === 5) {
            if (y === null) {
              y = g;
              try {
                l = g.stateNode, d ? (i = l.style, typeof i.setProperty == "function" ? i.setProperty("display", "none", "important") : i.display = "none") : (a = g.stateNode, u = g.memoizedProps.style, o = u != null && u.hasOwnProperty("display") ? u.display : null, a.style.display = lc("display", o));
              } catch (z) {
                ae(e, e.return, z);
              }
            }
          } else if (g.tag === 6) {
            if (y === null) try {
              g.stateNode.nodeValue = d ? "" : g.memoizedProps;
            } catch (z) {
              ae(e, e.return, z);
            }
          } else if ((g.tag !== 22 && g.tag !== 23 || g.memoizedState === null || g === e) && g.child !== null) {
            g.child.return = g, g = g.child;
            continue;
          }
          if (g === e) break e;
          for (; g.sibling === null; ) {
            if (g.return === null || g.return === e) break e;
            y === g && (y = null), g = g.return;
          }
          y === g && (y = null), g.sibling.return = g.return, g = g.sibling;
        }
      }
      break;
    case 19:
      Je(t, e), ot(e), r & 4 && xu(e);
      break;
    case 21:
      break;
    default:
      Je(
        t,
        e
      ), ot(e);
  }
}
function ot(e) {
  var t = e.flags;
  if (t & 2) {
    try {
      e: {
        for (var n = e.return; n !== null; ) {
          if (Pd(n)) {
            var r = n;
            break e;
          }
          n = n.return;
        }
        throw Error(S(160));
      }
      switch (r.tag) {
        case 5:
          var l = r.stateNode;
          r.flags & 32 && (Wr(l, ""), r.flags &= -33);
          var i = ku(e);
          rs(e, i, l);
          break;
        case 3:
        case 4:
          var o = r.stateNode.containerInfo, a = ku(e);
          ns(e, a, o);
          break;
        default:
          throw Error(S(161));
      }
    } catch (u) {
      ae(e, e.return, u);
    }
    e.flags &= -3;
  }
  t & 4096 && (e.flags &= -4097);
}
function Bm(e, t, n) {
  C = e, Wd(e);
}
function Wd(e, t, n) {
  for (var r = (e.mode & 1) !== 0; C !== null; ) {
    var l = C, i = l.child;
    if (l.tag === 22 && r) {
      var o = l.memoizedState !== null || Cl;
      if (!o) {
        var a = l.alternate, u = a !== null && a.memoizedState !== null || je;
        a = Cl;
        var d = je;
        if (Cl = o, (je = u) && !d) for (C = l; C !== null; ) o = C, u = o.child, o.tag === 22 && o.memoizedState !== null ? Su(l) : u !== null ? (u.return = o, C = u) : Su(l);
        for (; i !== null; ) C = i, Wd(i), i = i.sibling;
        C = l, Cl = a, je = d;
      }
      wu(e);
    } else l.subtreeFlags & 8772 && i !== null ? (i.return = l, C = i) : wu(e);
  }
}
function wu(e) {
  for (; C !== null; ) {
    var t = C;
    if (t.flags & 8772) {
      var n = t.alternate;
      try {
        if (t.flags & 8772) switch (t.tag) {
          case 0:
          case 11:
          case 15:
            je || Ni(5, t);
            break;
          case 1:
            var r = t.stateNode;
            if (t.flags & 4 && !je) if (n === null) r.componentDidMount();
            else {
              var l = t.elementType === t.type ? n.memoizedProps : qe(t.type, n.memoizedProps);
              r.componentDidUpdate(l, n.memoizedState, r.__reactInternalSnapshotBeforeUpdate);
            }
            var i = t.updateQueue;
            i !== null && iu(t, i, r);
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
              iu(t, o, n);
            }
            break;
          case 5:
            var a = t.stateNode;
            if (n === null && t.flags & 4) {
              n = a;
              var u = t.memoizedProps;
              switch (t.type) {
                case "button":
                case "input":
                case "select":
                case "textarea":
                  u.autoFocus && n.focus();
                  break;
                case "img":
                  u.src && (n.src = u.src);
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
                var y = d.memoizedState;
                if (y !== null) {
                  var g = y.dehydrated;
                  g !== null && Ir(g);
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
            throw Error(S(163));
        }
        je || t.flags & 512 && ts(t);
      } catch (h) {
        ae(t, t.return, h);
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
function _u(e) {
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
function Su(e) {
  for (; C !== null; ) {
    var t = C;
    try {
      switch (t.tag) {
        case 0:
        case 11:
        case 15:
          var n = t.return;
          try {
            Ni(4, t);
          } catch (u) {
            ae(t, n, u);
          }
          break;
        case 1:
          var r = t.stateNode;
          if (typeof r.componentDidMount == "function") {
            var l = t.return;
            try {
              r.componentDidMount();
            } catch (u) {
              ae(t, l, u);
            }
          }
          var i = t.return;
          try {
            ts(t);
          } catch (u) {
            ae(t, i, u);
          }
          break;
        case 5:
          var o = t.return;
          try {
            ts(t);
          } catch (u) {
            ae(t, o, u);
          }
      }
    } catch (u) {
      ae(t, t.return, u);
    }
    if (t === e) {
      C = null;
      break;
    }
    var a = t.sibling;
    if (a !== null) {
      a.return = t.return, C = a;
      break;
    }
    C = t.return;
  }
}
var $m = Math.ceil, mi = St.ReactCurrentDispatcher, Hs = St.ReactCurrentOwner, Ge = St.ReactCurrentBatchConfig, H = 0, ge = null, de = null, ke = 0, Ie = 0, Fn = Xt(0), pe = 0, Gr = null, fn = 0, Ci = 0, Qs = 0, Cr = null, Le = null, bs = 0, bn = 1 / 0, mt = null, hi = !1, ls = null, Kt = null, Pl = !1, It = null, gi = 0, Pr = 0, is = null, Vl = -1, Hl = 0;
function ze() {
  return H & 6 ? ue() : Vl !== -1 ? Vl : Vl = ue();
}
function Vt(e) {
  return e.mode & 1 ? H & 2 && ke !== 0 ? ke & -ke : jm.transition !== null ? (Hl === 0 && (Hl = vc()), Hl) : (e = X, e !== 0 || (e = window.event, e = e === void 0 ? 16 : jc(e.type)), e) : 1;
}
function rt(e, t, n, r) {
  if (50 < Pr) throw Pr = 0, is = null, Error(S(185));
  Zr(e, n, r), (!(H & 2) || e !== ge) && (e === ge && (!(H & 2) && (Ci |= n), pe === 4 && Dt(e, ke)), Oe(e, r), n === 1 && H === 0 && !(t.mode & 1) && (bn = ue() + 500, Ei && Yt()));
}
function Oe(e, t) {
  var n = e.callbackNode;
  jp(e, t);
  var r = ql(e, e === ge ? ke : 0);
  if (r === 0) n !== null && La(n), e.callbackNode = null, e.callbackPriority = 0;
  else if (t = r & -r, e.callbackPriority !== t) {
    if (n != null && La(n), t === 1) e.tag === 0 ? Sm(ju.bind(null, e)) : Kc(ju.bind(null, e)), km(function() {
      !(H & 6) && Yt();
    }), n = null;
    else {
      switch (yc(r)) {
        case 1:
          n = ks;
          break;
        case 4:
          n = hc;
          break;
        case 16:
          n = Jl;
          break;
        case 536870912:
          n = gc;
          break;
        default:
          n = Jl;
      }
      n = $d(n, Fd.bind(null, e));
    }
    e.callbackPriority = t, e.callbackNode = n;
  }
}
function Fd(e, t) {
  if (Vl = -1, Hl = 0, H & 6) throw Error(S(327));
  var n = e.callbackNode;
  if (Bn() && e.callbackNode !== n) return null;
  var r = ql(e, e === ge ? ke : 0);
  if (r === 0) return null;
  if (r & 30 || r & e.expiredLanes || t) t = vi(e, r);
  else {
    t = r;
    var l = H;
    H |= 2;
    var i = Od();
    (ge !== e || ke !== t) && (mt = null, bn = ue() + 500, sn(e, t));
    do
      try {
        Vm();
        break;
      } catch (a) {
        Dd(e, a);
      }
    while (!0);
    Ls(), mi.current = i, H = l, de !== null ? t = 0 : (ge = null, ke = 0, t = pe);
  }
  if (t !== 0) {
    if (t === 2 && (l = Lo(e), l !== 0 && (r = l, t = os(e, l))), t === 1) throw n = Gr, sn(e, 0), Dt(e, r), Oe(e, ue()), n;
    if (t === 6) Dt(e, r);
    else {
      if (l = e.current.alternate, !(r & 30) && !Um(l) && (t = vi(e, r), t === 2 && (i = Lo(e), i !== 0 && (r = i, t = os(e, i))), t === 1)) throw n = Gr, sn(e, 0), Dt(e, r), Oe(e, ue()), n;
      switch (e.finishedWork = l, e.finishedLanes = r, t) {
        case 0:
        case 1:
          throw Error(S(345));
        case 2:
          tn(e, Le, mt);
          break;
        case 3:
          if (Dt(e, r), (r & 130023424) === r && (t = bs + 500 - ue(), 10 < t)) {
            if (ql(e, 0) !== 0) break;
            if (l = e.suspendedLanes, (l & r) !== r) {
              ze(), e.pingedLanes |= e.suspendedLanes & l;
              break;
            }
            e.timeoutHandle = Bo(tn.bind(null, e, Le, mt), t);
            break;
          }
          tn(e, Le, mt);
          break;
        case 4:
          if (Dt(e, r), (r & 4194240) === r) break;
          for (t = e.eventTimes, l = -1; 0 < r; ) {
            var o = 31 - nt(r);
            i = 1 << o, o = t[o], o > l && (l = o), r &= ~i;
          }
          if (r = l, r = ue() - r, r = (120 > r ? 120 : 480 > r ? 480 : 1080 > r ? 1080 : 1920 > r ? 1920 : 3e3 > r ? 3e3 : 4320 > r ? 4320 : 1960 * $m(r / 1960)) - r, 10 < r) {
            e.timeoutHandle = Bo(tn.bind(null, e, Le, mt), r);
            break;
          }
          tn(e, Le, mt);
          break;
        case 5:
          tn(e, Le, mt);
          break;
        default:
          throw Error(S(329));
      }
    }
  }
  return Oe(e, ue()), e.callbackNode === n ? Fd.bind(null, e) : null;
}
function os(e, t) {
  var n = Cr;
  return e.current.memoizedState.isDehydrated && (sn(e, t).flags |= 256), e = vi(e, t), e !== 2 && (t = Le, Le = n, t !== null && ss(t)), e;
}
function ss(e) {
  Le === null ? Le = e : Le.push.apply(Le, e);
}
function Um(e) {
  for (var t = e; ; ) {
    if (t.flags & 16384) {
      var n = t.updateQueue;
      if (n !== null && (n = n.stores, n !== null)) for (var r = 0; r < n.length; r++) {
        var l = n[r], i = l.getSnapshot;
        l = l.value;
        try {
          if (!lt(i(), l)) return !1;
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
function Dt(e, t) {
  for (t &= ~Qs, t &= ~Ci, e.suspendedLanes |= t, e.pingedLanes &= ~t, e = e.expirationTimes; 0 < t; ) {
    var n = 31 - nt(t), r = 1 << n;
    e[n] = -1, t &= ~r;
  }
}
function ju(e) {
  if (H & 6) throw Error(S(327));
  Bn();
  var t = ql(e, 0);
  if (!(t & 1)) return Oe(e, ue()), null;
  var n = vi(e, t);
  if (e.tag !== 0 && n === 2) {
    var r = Lo(e);
    r !== 0 && (t = r, n = os(e, r));
  }
  if (n === 1) throw n = Gr, sn(e, 0), Dt(e, t), Oe(e, ue()), n;
  if (n === 6) throw Error(S(345));
  return e.finishedWork = e.current.alternate, e.finishedLanes = t, tn(e, Le, mt), Oe(e, ue()), null;
}
function Gs(e, t) {
  var n = H;
  H |= 1;
  try {
    return e(t);
  } finally {
    H = n, H === 0 && (bn = ue() + 500, Ei && Yt());
  }
}
function pn(e) {
  It !== null && It.tag === 0 && !(H & 6) && Bn();
  var t = H;
  H |= 1;
  var n = Ge.transition, r = X;
  try {
    if (Ge.transition = null, X = 1, e) return e();
  } finally {
    X = r, Ge.transition = n, H = t, !(H & 6) && Yt();
  }
}
function Xs() {
  Ie = Fn.current, J(Fn);
}
function sn(e, t) {
  e.finishedWork = null, e.finishedLanes = 0;
  var n = e.timeoutHandle;
  if (n !== -1 && (e.timeoutHandle = -1, ym(n)), de !== null) for (n = de.return; n !== null; ) {
    var r = n;
    switch (Cs(r), r.tag) {
      case 1:
        r = r.type.childContextTypes, r != null && li();
        break;
      case 3:
        Hn(), J(Fe), J(Ee), Ms();
        break;
      case 5:
        Is(r);
        break;
      case 4:
        Hn();
        break;
      case 13:
        J(re);
        break;
      case 19:
        J(re);
        break;
      case 10:
        Ws(r.type._context);
        break;
      case 22:
      case 23:
        Xs();
    }
    n = n.return;
  }
  if (ge = e, de = e = Ht(e.current, null), ke = Ie = t, pe = 0, Gr = null, Qs = Ci = fn = 0, Le = Cr = null, ln !== null) {
    for (t = 0; t < ln.length; t++) if (n = ln[t], r = n.interleaved, r !== null) {
      n.interleaved = null;
      var l = r.next, i = n.pending;
      if (i !== null) {
        var o = i.next;
        i.next = l, r.next = o;
      }
      n.pending = r;
    }
    ln = null;
  }
  return e;
}
function Dd(e, t) {
  do {
    var n = de;
    try {
      if (Ls(), $l.current = pi, fi) {
        for (var r = le.memoizedState; r !== null; ) {
          var l = r.queue;
          l !== null && (l.pending = null), r = r.next;
        }
        fi = !1;
      }
      if (dn = 0, me = fe = le = null, zr = !1, Hr = 0, Hs.current = null, n === null || n.return === null) {
        pe = 1, Gr = t, de = null;
        break;
      }
      e: {
        var i = e, o = n.return, a = n, u = t;
        if (t = ke, a.flags |= 32768, u !== null && typeof u == "object" && typeof u.then == "function") {
          var d = u, y = a, g = y.tag;
          if (!(y.mode & 1) && (g === 0 || g === 11 || g === 15)) {
            var h = y.alternate;
            h ? (y.updateQueue = h.updateQueue, y.memoizedState = h.memoizedState, y.lanes = h.lanes) : (y.updateQueue = null, y.memoizedState = null);
          }
          var k = du(o);
          if (k !== null) {
            k.flags &= -257, fu(k, o, a, i, t), k.mode & 1 && cu(i, d, t), t = k, u = d;
            var E = t.updateQueue;
            if (E === null) {
              var z = /* @__PURE__ */ new Set();
              z.add(u), t.updateQueue = z;
            } else E.add(u);
            break e;
          } else {
            if (!(t & 1)) {
              cu(i, d, t), Ys();
              break e;
            }
            u = Error(S(426));
          }
        } else if (q && a.mode & 1) {
          var Q = du(o);
          if (Q !== null) {
            !(Q.flags & 65536) && (Q.flags |= 256), fu(Q, o, a, i, t), Ps(Qn(u, a));
            break e;
          }
        }
        i = u = Qn(u, a), pe !== 4 && (pe = 2), Cr === null ? Cr = [i] : Cr.push(i), i = o;
        do {
          switch (i.tag) {
            case 3:
              i.flags |= 65536, t &= -t, i.lanes |= t;
              var p = yd(i, u, t);
              lu(i, p);
              break e;
            case 1:
              a = u;
              var c = i.type, m = i.stateNode;
              if (!(i.flags & 128) && (typeof c.getDerivedStateFromError == "function" || m !== null && typeof m.componentDidCatch == "function" && (Kt === null || !Kt.has(m)))) {
                i.flags |= 65536, t &= -t, i.lanes |= t;
                var _ = kd(i, a, t);
                lu(i, _);
                break e;
              }
          }
          i = i.return;
        } while (i !== null);
      }
      Md(n);
    } catch (j) {
      t = j, de === n && n !== null && (de = n = n.return);
      continue;
    }
    break;
  } while (!0);
}
function Od() {
  var e = mi.current;
  return mi.current = pi, e === null ? pi : e;
}
function Ys() {
  (pe === 0 || pe === 3 || pe === 2) && (pe = 4), ge === null || !(fn & 268435455) && !(Ci & 268435455) || Dt(ge, ke);
}
function vi(e, t) {
  var n = H;
  H |= 2;
  var r = Od();
  (ge !== e || ke !== t) && (mt = null, sn(e, t));
  do
    try {
      Km();
      break;
    } catch (l) {
      Dd(e, l);
    }
  while (!0);
  if (Ls(), H = n, mi.current = r, de !== null) throw Error(S(261));
  return ge = null, ke = 0, pe;
}
function Km() {
  for (; de !== null; ) Id(de);
}
function Vm() {
  for (; de !== null && !hp(); ) Id(de);
}
function Id(e) {
  var t = Bd(e.alternate, e, Ie);
  e.memoizedProps = e.pendingProps, t === null ? Md(e) : de = t, Hs.current = null;
}
function Md(e) {
  var t = e;
  do {
    var n = t.alternate;
    if (e = t.return, t.flags & 32768) {
      if (n = Im(n, t), n !== null) {
        n.flags &= 32767, de = n;
        return;
      }
      if (e !== null) e.flags |= 32768, e.subtreeFlags = 0, e.deletions = null;
      else {
        pe = 6, de = null;
        return;
      }
    } else if (n = Om(n, t, Ie), n !== null) {
      de = n;
      return;
    }
    if (t = t.sibling, t !== null) {
      de = t;
      return;
    }
    de = t = e;
  } while (t !== null);
  pe === 0 && (pe = 5);
}
function tn(e, t, n) {
  var r = X, l = Ge.transition;
  try {
    Ge.transition = null, X = 1, Hm(e, t, n, r);
  } finally {
    Ge.transition = l, X = r;
  }
  return null;
}
function Hm(e, t, n, r) {
  do
    Bn();
  while (It !== null);
  if (H & 6) throw Error(S(327));
  n = e.finishedWork;
  var l = e.finishedLanes;
  if (n === null) return null;
  if (e.finishedWork = null, e.finishedLanes = 0, n === e.current) throw Error(S(177));
  e.callbackNode = null, e.callbackPriority = 0;
  var i = n.lanes | n.childLanes;
  if (Ep(e, i), e === ge && (de = ge = null, ke = 0), !(n.subtreeFlags & 2064) && !(n.flags & 2064) || Pl || (Pl = !0, $d(Jl, function() {
    return Bn(), null;
  })), i = (n.flags & 15990) !== 0, n.subtreeFlags & 15990 || i) {
    i = Ge.transition, Ge.transition = null;
    var o = X;
    X = 1;
    var a = H;
    H |= 4, Hs.current = null, Rm(e, n), Ld(n, e), dm(Mo), ei = !!Io, Mo = Io = null, e.current = n, Bm(n), gp(), H = a, X = o, Ge.transition = i;
  } else e.current = n;
  if (Pl && (Pl = !1, It = e, gi = l), i = e.pendingLanes, i === 0 && (Kt = null), kp(n.stateNode), Oe(e, ue()), t !== null) for (r = e.onRecoverableError, n = 0; n < t.length; n++) l = t[n], r(l.value, { componentStack: l.stack, digest: l.digest });
  if (hi) throw hi = !1, e = ls, ls = null, e;
  return gi & 1 && e.tag !== 0 && Bn(), i = e.pendingLanes, i & 1 ? e === is ? Pr++ : (Pr = 0, is = e) : Pr = 0, Yt(), null;
}
function Bn() {
  if (It !== null) {
    var e = yc(gi), t = Ge.transition, n = X;
    try {
      if (Ge.transition = null, X = 16 > e ? 16 : e, It === null) var r = !1;
      else {
        if (e = It, It = null, gi = 0, H & 6) throw Error(S(331));
        var l = H;
        for (H |= 4, C = e.current; C !== null; ) {
          var i = C, o = i.child;
          if (C.flags & 16) {
            var a = i.deletions;
            if (a !== null) {
              for (var u = 0; u < a.length; u++) {
                var d = a[u];
                for (C = d; C !== null; ) {
                  var y = C;
                  switch (y.tag) {
                    case 0:
                    case 11:
                    case 15:
                      Nr(8, y, i);
                  }
                  var g = y.child;
                  if (g !== null) g.return = y, C = g;
                  else for (; C !== null; ) {
                    y = C;
                    var h = y.sibling, k = y.return;
                    if (Cd(y), y === d) {
                      C = null;
                      break;
                    }
                    if (h !== null) {
                      h.return = k, C = h;
                      break;
                    }
                    C = k;
                  }
                }
              }
              var E = i.alternate;
              if (E !== null) {
                var z = E.child;
                if (z !== null) {
                  E.child = null;
                  do {
                    var Q = z.sibling;
                    z.sibling = null, z = Q;
                  } while (z !== null);
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
                Nr(9, i, i.return);
            }
            var p = i.sibling;
            if (p !== null) {
              p.return = i.return, C = p;
              break e;
            }
            C = i.return;
          }
        }
        var c = e.current;
        for (C = c; C !== null; ) {
          o = C;
          var m = o.child;
          if (o.subtreeFlags & 2064 && m !== null) m.return = o, C = m;
          else e: for (o = c; C !== null; ) {
            if (a = C, a.flags & 2048) try {
              switch (a.tag) {
                case 0:
                case 11:
                case 15:
                  Ni(9, a);
              }
            } catch (j) {
              ae(a, a.return, j);
            }
            if (a === o) {
              C = null;
              break e;
            }
            var _ = a.sibling;
            if (_ !== null) {
              _.return = a.return, C = _;
              break e;
            }
            C = a.return;
          }
        }
        if (H = l, Yt(), ut && typeof ut.onPostCommitFiberRoot == "function") try {
          ut.onPostCommitFiberRoot(xi, e);
        } catch {
        }
        r = !0;
      }
      return r;
    } finally {
      X = n, Ge.transition = t;
    }
  }
  return !1;
}
function Eu(e, t, n) {
  t = Qn(n, t), t = yd(e, t, 1), e = Ut(e, t, 1), t = ze(), e !== null && (Zr(e, 1, t), Oe(e, t));
}
function ae(e, t, n) {
  if (e.tag === 3) Eu(e, e, n);
  else for (; t !== null; ) {
    if (t.tag === 3) {
      Eu(t, e, n);
      break;
    } else if (t.tag === 1) {
      var r = t.stateNode;
      if (typeof t.type.getDerivedStateFromError == "function" || typeof r.componentDidCatch == "function" && (Kt === null || !Kt.has(r))) {
        e = Qn(n, e), e = kd(t, e, 1), t = Ut(t, e, 1), e = ze(), t !== null && (Zr(t, 1, e), Oe(t, e));
        break;
      }
    }
    t = t.return;
  }
}
function Qm(e, t, n) {
  var r = e.pingCache;
  r !== null && r.delete(t), t = ze(), e.pingedLanes |= e.suspendedLanes & n, ge === e && (ke & n) === n && (pe === 4 || pe === 3 && (ke & 130023424) === ke && 500 > ue() - bs ? sn(e, 0) : Qs |= n), Oe(e, t);
}
function Rd(e, t) {
  t === 0 && (e.mode & 1 ? (t = xl, xl <<= 1, !(xl & 130023424) && (xl = 4194304)) : t = 1);
  var n = ze();
  e = wt(e, t), e !== null && (Zr(e, t, n), Oe(e, n));
}
function bm(e) {
  var t = e.memoizedState, n = 0;
  t !== null && (n = t.retryLane), Rd(e, n);
}
function Gm(e, t) {
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
      throw Error(S(314));
  }
  r !== null && r.delete(t), Rd(e, n);
}
var Bd;
Bd = function(e, t, n) {
  if (e !== null) if (e.memoizedProps !== t.pendingProps || Fe.current) We = !0;
  else {
    if (!(e.lanes & n) && !(t.flags & 128)) return We = !1, Dm(e, t, n);
    We = !!(e.flags & 131072);
  }
  else We = !1, q && t.flags & 1048576 && Vc(t, si, t.index);
  switch (t.lanes = 0, t.tag) {
    case 2:
      var r = t.type;
      Kl(e, t), e = t.pendingProps;
      var l = Un(t, Ee.current);
      Rn(t, n), l = Bs(null, t, r, e, l, n);
      var i = $s();
      return t.flags |= 1, typeof l == "object" && l !== null && typeof l.render == "function" && l.$$typeof === void 0 ? (t.tag = 1, t.memoizedState = null, t.updateQueue = null, De(r) ? (i = !0, ii(t)) : i = !1, t.memoizedState = l.state !== null && l.state !== void 0 ? l.state : null, Ds(t), l.updater = zi, t.stateNode = l, l._reactInternals = t, bo(t, r, e, n), t = Yo(null, t, r, !0, i, n)) : (t.tag = 0, q && i && Ns(t), Te(null, t, l, n), t = t.child), t;
    case 16:
      r = t.elementType;
      e: {
        switch (Kl(e, t), e = t.pendingProps, l = r._init, r = l(r._payload), t.type = r, l = t.tag = Ym(r), e = qe(r, e), l) {
          case 0:
            t = Xo(null, t, r, e, n);
            break e;
          case 1:
            t = hu(null, t, r, e, n);
            break e;
          case 11:
            t = pu(null, t, r, e, n);
            break e;
          case 14:
            t = mu(null, t, r, qe(r.type, e), n);
            break e;
        }
        throw Error(S(
          306,
          r,
          ""
        ));
      }
      return t;
    case 0:
      return r = t.type, l = t.pendingProps, l = t.elementType === r ? l : qe(r, l), Xo(e, t, r, l, n);
    case 1:
      return r = t.type, l = t.pendingProps, l = t.elementType === r ? l : qe(r, l), hu(e, t, r, l, n);
    case 3:
      e: {
        if (Sd(t), e === null) throw Error(S(387));
        r = t.pendingProps, i = t.memoizedState, l = i.element, Yc(e, t), ci(t, r, null, n);
        var o = t.memoizedState;
        if (r = o.element, i.isDehydrated) if (i = { element: r, isDehydrated: !1, cache: o.cache, pendingSuspenseBoundaries: o.pendingSuspenseBoundaries, transitions: o.transitions }, t.updateQueue.baseState = i, t.memoizedState = i, t.flags & 256) {
          l = Qn(Error(S(423)), t), t = gu(e, t, r, n, l);
          break e;
        } else if (r !== l) {
          l = Qn(Error(S(424)), t), t = gu(e, t, r, n, l);
          break e;
        } else for (Me = $t(t.stateNode.containerInfo.firstChild), Re = t, q = !0, tt = null, n = Gc(t, null, r, n), t.child = n; n; ) n.flags = n.flags & -3 | 4096, n = n.sibling;
        else {
          if (Kn(), r === l) {
            t = _t(e, t, n);
            break e;
          }
          Te(e, t, r, n);
        }
        t = t.child;
      }
      return t;
    case 5:
      return Zc(t), e === null && Vo(t), r = t.type, l = t.pendingProps, i = e !== null ? e.memoizedProps : null, o = l.children, Ro(r, l) ? o = null : i !== null && Ro(r, i) && (t.flags |= 32), _d(e, t), Te(e, t, o, n), t.child;
    case 6:
      return e === null && Vo(t), null;
    case 13:
      return jd(e, t, n);
    case 4:
      return Os(t, t.stateNode.containerInfo), r = t.pendingProps, e === null ? t.child = Vn(t, null, r, n) : Te(e, t, r, n), t.child;
    case 11:
      return r = t.type, l = t.pendingProps, l = t.elementType === r ? l : qe(r, l), pu(e, t, r, l, n);
    case 7:
      return Te(e, t, t.pendingProps, n), t.child;
    case 8:
      return Te(e, t, t.pendingProps.children, n), t.child;
    case 12:
      return Te(e, t, t.pendingProps.children, n), t.child;
    case 10:
      e: {
        if (r = t.type._context, l = t.pendingProps, i = t.memoizedProps, o = l.value, Y(ai, r._currentValue), r._currentValue = o, i !== null) if (lt(i.value, o)) {
          if (i.children === l.children && !Fe.current) {
            t = _t(e, t, n);
            break e;
          }
        } else for (i = t.child, i !== null && (i.return = t); i !== null; ) {
          var a = i.dependencies;
          if (a !== null) {
            o = i.child;
            for (var u = a.firstContext; u !== null; ) {
              if (u.context === r) {
                if (i.tag === 1) {
                  u = yt(-1, n & -n), u.tag = 2;
                  var d = i.updateQueue;
                  if (d !== null) {
                    d = d.shared;
                    var y = d.pending;
                    y === null ? u.next = u : (u.next = y.next, y.next = u), d.pending = u;
                  }
                }
                i.lanes |= n, u = i.alternate, u !== null && (u.lanes |= n), Ho(
                  i.return,
                  n,
                  t
                ), a.lanes |= n;
                break;
              }
              u = u.next;
            }
          } else if (i.tag === 10) o = i.type === t.type ? null : i.child;
          else if (i.tag === 18) {
            if (o = i.return, o === null) throw Error(S(341));
            o.lanes |= n, a = o.alternate, a !== null && (a.lanes |= n), Ho(o, n, t), o = i.sibling;
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
        Te(e, t, l.children, n), t = t.child;
      }
      return t;
    case 9:
      return l = t.type, r = t.pendingProps.children, Rn(t, n), l = Xe(l), r = r(l), t.flags |= 1, Te(e, t, r, n), t.child;
    case 14:
      return r = t.type, l = qe(r, t.pendingProps), l = qe(r.type, l), mu(e, t, r, l, n);
    case 15:
      return xd(e, t, t.type, t.pendingProps, n);
    case 17:
      return r = t.type, l = t.pendingProps, l = t.elementType === r ? l : qe(r, l), Kl(e, t), t.tag = 1, De(r) ? (e = !0, ii(t)) : e = !1, Rn(t, n), vd(t, r, l), bo(t, r, l, n), Yo(null, t, r, !0, e, n);
    case 19:
      return Ed(e, t, n);
    case 22:
      return wd(e, t, n);
  }
  throw Error(S(156, t.tag));
};
function $d(e, t) {
  return mc(e, t);
}
function Xm(e, t, n, r) {
  this.tag = e, this.key = n, this.sibling = this.child = this.return = this.stateNode = this.type = this.elementType = null, this.index = 0, this.ref = null, this.pendingProps = t, this.dependencies = this.memoizedState = this.updateQueue = this.memoizedProps = null, this.mode = r, this.subtreeFlags = this.flags = 0, this.deletions = null, this.childLanes = this.lanes = 0, this.alternate = null;
}
function be(e, t, n, r) {
  return new Xm(e, t, n, r);
}
function Zs(e) {
  return e = e.prototype, !(!e || !e.isReactComponent);
}
function Ym(e) {
  if (typeof e == "function") return Zs(e) ? 1 : 0;
  if (e != null) {
    if (e = e.$$typeof, e === gs) return 11;
    if (e === vs) return 14;
  }
  return 2;
}
function Ht(e, t) {
  var n = e.alternate;
  return n === null ? (n = be(e.tag, t, e.key, e.mode), n.elementType = e.elementType, n.type = e.type, n.stateNode = e.stateNode, n.alternate = e, e.alternate = n) : (n.pendingProps = t, n.type = e.type, n.flags = 0, n.subtreeFlags = 0, n.deletions = null), n.flags = e.flags & 14680064, n.childLanes = e.childLanes, n.lanes = e.lanes, n.child = e.child, n.memoizedProps = e.memoizedProps, n.memoizedState = e.memoizedState, n.updateQueue = e.updateQueue, t = e.dependencies, n.dependencies = t === null ? null : { lanes: t.lanes, firstContext: t.firstContext }, n.sibling = e.sibling, n.index = e.index, n.ref = e.ref, n;
}
function Ql(e, t, n, r, l, i) {
  var o = 2;
  if (r = e, typeof e == "function") Zs(e) && (o = 1);
  else if (typeof e == "string") o = 5;
  else e: switch (e) {
    case jn:
      return an(n.children, l, i, t);
    case hs:
      o = 8, l |= 8;
      break;
    case vo:
      return e = be(12, n, t, l | 2), e.elementType = vo, e.lanes = i, e;
    case yo:
      return e = be(13, n, t, l), e.elementType = yo, e.lanes = i, e;
    case ko:
      return e = be(19, n, t, l), e.elementType = ko, e.lanes = i, e;
    case Zu:
      return Pi(n, l, i, t);
    default:
      if (typeof e == "object" && e !== null) switch (e.$$typeof) {
        case Xu:
          o = 10;
          break e;
        case Yu:
          o = 9;
          break e;
        case gs:
          o = 11;
          break e;
        case vs:
          o = 14;
          break e;
        case Lt:
          o = 16, r = null;
          break e;
      }
      throw Error(S(130, e == null ? e : typeof e, ""));
  }
  return t = be(o, n, t, l), t.elementType = e, t.type = r, t.lanes = i, t;
}
function an(e, t, n, r) {
  return e = be(7, e, r, t), e.lanes = n, e;
}
function Pi(e, t, n, r) {
  return e = be(22, e, r, t), e.elementType = Zu, e.lanes = n, e.stateNode = { isHidden: !1 }, e;
}
function co(e, t, n) {
  return e = be(6, e, null, t), e.lanes = n, e;
}
function fo(e, t, n) {
  return t = be(4, e.children !== null ? e.children : [], e.key, t), t.lanes = n, t.stateNode = { containerInfo: e.containerInfo, pendingChildren: null, implementation: e.implementation }, t;
}
function Zm(e, t, n, r, l) {
  this.tag = t, this.containerInfo = e, this.finishedWork = this.pingCache = this.current = this.pendingChildren = null, this.timeoutHandle = -1, this.callbackNode = this.pendingContext = this.context = null, this.callbackPriority = 0, this.eventTimes = Hi(0), this.expirationTimes = Hi(-1), this.entangledLanes = this.finishedLanes = this.mutableReadLanes = this.expiredLanes = this.pingedLanes = this.suspendedLanes = this.pendingLanes = 0, this.entanglements = Hi(0), this.identifierPrefix = r, this.onRecoverableError = l, this.mutableSourceEagerHydrationData = null;
}
function Js(e, t, n, r, l, i, o, a, u) {
  return e = new Zm(e, t, n, a, u), t === 1 ? (t = 1, i === !0 && (t |= 8)) : t = 0, i = be(3, null, null, t), e.current = i, i.stateNode = e, i.memoizedState = { element: r, isDehydrated: n, cache: null, transitions: null, pendingSuspenseBoundaries: null }, Ds(i), e;
}
function Jm(e, t, n) {
  var r = 3 < arguments.length && arguments[3] !== void 0 ? arguments[3] : null;
  return { $$typeof: Sn, key: r == null ? null : "" + r, children: e, containerInfo: t, implementation: n };
}
function Ud(e) {
  if (!e) return bt;
  e = e._reactInternals;
  e: {
    if (hn(e) !== e || e.tag !== 1) throw Error(S(170));
    var t = e;
    do {
      switch (t.tag) {
        case 3:
          t = t.stateNode.context;
          break e;
        case 1:
          if (De(t.type)) {
            t = t.stateNode.__reactInternalMemoizedMergedChildContext;
            break e;
          }
      }
      t = t.return;
    } while (t !== null);
    throw Error(S(171));
  }
  if (e.tag === 1) {
    var n = e.type;
    if (De(n)) return Uc(e, n, t);
  }
  return t;
}
function Kd(e, t, n, r, l, i, o, a, u) {
  return e = Js(n, r, !0, e, l, i, o, a, u), e.context = Ud(null), n = e.current, r = ze(), l = Vt(n), i = yt(r, l), i.callback = t ?? null, Ut(n, i, l), e.current.lanes = l, Zr(e, l, r), Oe(e, r), e;
}
function Ai(e, t, n, r) {
  var l = t.current, i = ze(), o = Vt(l);
  return n = Ud(n), t.context === null ? t.context = n : t.pendingContext = n, t = yt(i, o), t.payload = { element: e }, r = r === void 0 ? null : r, r !== null && (t.callback = r), e = Ut(l, t, o), e !== null && (rt(e, l, o, i), Bl(e, l, o)), o;
}
function yi(e) {
  if (e = e.current, !e.child) return null;
  switch (e.child.tag) {
    case 5:
      return e.child.stateNode;
    default:
      return e.child.stateNode;
  }
}
function Tu(e, t) {
  if (e = e.memoizedState, e !== null && e.dehydrated !== null) {
    var n = e.retryLane;
    e.retryLane = n !== 0 && n < t ? n : t;
  }
}
function qs(e, t) {
  Tu(e, t), (e = e.alternate) && Tu(e, t);
}
function qm() {
  return null;
}
var Vd = typeof reportError == "function" ? reportError : function(e) {
  console.error(e);
};
function ea(e) {
  this._internalRoot = e;
}
Li.prototype.render = ea.prototype.render = function(e) {
  var t = this._internalRoot;
  if (t === null) throw Error(S(409));
  Ai(e, t, null, null);
};
Li.prototype.unmount = ea.prototype.unmount = function() {
  var e = this._internalRoot;
  if (e !== null) {
    this._internalRoot = null;
    var t = e.containerInfo;
    pn(function() {
      Ai(null, e, null, null);
    }), t[xt] = null;
  }
};
function Li(e) {
  this._internalRoot = e;
}
Li.prototype.unstable_scheduleHydration = function(e) {
  if (e) {
    var t = wc();
    e = { blockedOn: null, target: e, priority: t };
    for (var n = 0; n < Ft.length && t !== 0 && t < Ft[n].priority; n++) ;
    Ft.splice(n, 0, e), n === 0 && Sc(e);
  }
};
function ta(e) {
  return !(!e || e.nodeType !== 1 && e.nodeType !== 9 && e.nodeType !== 11);
}
function Wi(e) {
  return !(!e || e.nodeType !== 1 && e.nodeType !== 9 && e.nodeType !== 11 && (e.nodeType !== 8 || e.nodeValue !== " react-mount-point-unstable "));
}
function zu() {
}
function eh(e, t, n, r, l) {
  if (l) {
    if (typeof r == "function") {
      var i = r;
      r = function() {
        var d = yi(o);
        i.call(d);
      };
    }
    var o = Kd(t, r, e, 0, null, !1, !1, "", zu);
    return e._reactRootContainer = o, e[xt] = o.current, Br(e.nodeType === 8 ? e.parentNode : e), pn(), o;
  }
  for (; l = e.lastChild; ) e.removeChild(l);
  if (typeof r == "function") {
    var a = r;
    r = function() {
      var d = yi(u);
      a.call(d);
    };
  }
  var u = Js(e, 0, !1, null, null, !1, !1, "", zu);
  return e._reactRootContainer = u, e[xt] = u.current, Br(e.nodeType === 8 ? e.parentNode : e), pn(function() {
    Ai(t, u, n, r);
  }), u;
}
function Fi(e, t, n, r, l) {
  var i = n._reactRootContainer;
  if (i) {
    var o = i;
    if (typeof l == "function") {
      var a = l;
      l = function() {
        var u = yi(o);
        a.call(u);
      };
    }
    Ai(t, o, e, l);
  } else o = eh(n, t, e, l, r);
  return yi(o);
}
kc = function(e) {
  switch (e.tag) {
    case 3:
      var t = e.stateNode;
      if (t.current.memoizedState.isDehydrated) {
        var n = kr(t.pendingLanes);
        n !== 0 && (xs(t, n | 1), Oe(t, ue()), !(H & 6) && (bn = ue() + 500, Yt()));
      }
      break;
    case 13:
      pn(function() {
        var r = wt(e, 1);
        if (r !== null) {
          var l = ze();
          rt(r, e, 1, l);
        }
      }), qs(e, 1);
  }
};
ws = function(e) {
  if (e.tag === 13) {
    var t = wt(e, 134217728);
    if (t !== null) {
      var n = ze();
      rt(t, e, 134217728, n);
    }
    qs(e, 134217728);
  }
};
xc = function(e) {
  if (e.tag === 13) {
    var t = Vt(e), n = wt(e, t);
    if (n !== null) {
      var r = ze();
      rt(n, e, t, r);
    }
    qs(e, t);
  }
};
wc = function() {
  return X;
};
_c = function(e, t) {
  var n = X;
  try {
    return X = e, t();
  } finally {
    X = n;
  }
};
Co = function(e, t, n) {
  switch (t) {
    case "input":
      if (_o(e, n), t = n.name, n.type === "radio" && t != null) {
        for (n = e; n.parentNode; ) n = n.parentNode;
        for (n = n.querySelectorAll("input[name=" + JSON.stringify("" + t) + '][type="radio"]'), t = 0; t < n.length; t++) {
          var r = n[t];
          if (r !== e && r.form === e.form) {
            var l = ji(r);
            if (!l) throw Error(S(90));
            qu(r), _o(r, l);
          }
        }
      }
      break;
    case "textarea":
      tc(e, n);
      break;
    case "select":
      t = n.value, t != null && Dn(e, !!n.multiple, t, !1);
  }
};
ac = Gs;
uc = pn;
var th = { usingClientEntryPoint: !1, Events: [qr, Nn, ji, oc, sc, Gs] }, mr = { findFiberByHostInstance: rn, bundleType: 0, version: "18.3.1", rendererPackageName: "react-dom" }, nh = { bundleType: mr.bundleType, version: mr.version, rendererPackageName: mr.rendererPackageName, rendererConfig: mr.rendererConfig, overrideHookState: null, overrideHookStateDeletePath: null, overrideHookStateRenamePath: null, overrideProps: null, overridePropsDeletePath: null, overridePropsRenamePath: null, setErrorHandler: null, setSuspenseHandler: null, scheduleUpdate: null, currentDispatcherRef: St.ReactCurrentDispatcher, findHostInstanceByFiber: function(e) {
  return e = fc(e), e === null ? null : e.stateNode;
}, findFiberByHostInstance: mr.findFiberByHostInstance || qm, findHostInstancesForRefresh: null, scheduleRefresh: null, scheduleRoot: null, setRefreshHandler: null, getCurrentFiber: null, reconcilerVersion: "18.3.1-next-f1338f8080-20240426" };
if (typeof __REACT_DEVTOOLS_GLOBAL_HOOK__ < "u") {
  var Al = __REACT_DEVTOOLS_GLOBAL_HOOK__;
  if (!Al.isDisabled && Al.supportsFiber) try {
    xi = Al.inject(nh), ut = Al;
  } catch {
  }
}
$e.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED = th;
$e.createPortal = function(e, t) {
  var n = 2 < arguments.length && arguments[2] !== void 0 ? arguments[2] : null;
  if (!ta(t)) throw Error(S(200));
  return Jm(e, t, null, n);
};
$e.createRoot = function(e, t) {
  if (!ta(e)) throw Error(S(299));
  var n = !1, r = "", l = Vd;
  return t != null && (t.unstable_strictMode === !0 && (n = !0), t.identifierPrefix !== void 0 && (r = t.identifierPrefix), t.onRecoverableError !== void 0 && (l = t.onRecoverableError)), t = Js(e, 1, !1, null, null, n, !1, r, l), e[xt] = t.current, Br(e.nodeType === 8 ? e.parentNode : e), new ea(t);
};
$e.findDOMNode = function(e) {
  if (e == null) return null;
  if (e.nodeType === 1) return e;
  var t = e._reactInternals;
  if (t === void 0)
    throw typeof e.render == "function" ? Error(S(188)) : (e = Object.keys(e).join(","), Error(S(268, e)));
  return e = fc(t), e = e === null ? null : e.stateNode, e;
};
$e.flushSync = function(e) {
  return pn(e);
};
$e.hydrate = function(e, t, n) {
  if (!Wi(t)) throw Error(S(200));
  return Fi(null, e, t, !0, n);
};
$e.hydrateRoot = function(e, t, n) {
  if (!ta(e)) throw Error(S(405));
  var r = n != null && n.hydratedSources || null, l = !1, i = "", o = Vd;
  if (n != null && (n.unstable_strictMode === !0 && (l = !0), n.identifierPrefix !== void 0 && (i = n.identifierPrefix), n.onRecoverableError !== void 0 && (o = n.onRecoverableError)), t = Kd(t, null, e, 1, n ?? null, l, !1, i, o), e[xt] = t.current, Br(e), r) for (e = 0; e < r.length; e++) n = r[e], l = n._getVersion, l = l(n._source), t.mutableSourceEagerHydrationData == null ? t.mutableSourceEagerHydrationData = [n, l] : t.mutableSourceEagerHydrationData.push(
    n,
    l
  );
  return new Li(t);
};
$e.render = function(e, t, n) {
  if (!Wi(t)) throw Error(S(200));
  return Fi(null, e, t, !1, n);
};
$e.unmountComponentAtNode = function(e) {
  if (!Wi(e)) throw Error(S(40));
  return e._reactRootContainer ? (pn(function() {
    Fi(null, null, e, !1, function() {
      e._reactRootContainer = null, e[xt] = null;
    });
  }), !0) : !1;
};
$e.unstable_batchedUpdates = Gs;
$e.unstable_renderSubtreeIntoContainer = function(e, t, n, r) {
  if (!Wi(n)) throw Error(S(200));
  if (e == null || e._reactInternals === void 0) throw Error(S(38));
  return Fi(e, t, n, !1, r);
};
$e.version = "18.3.1-next-f1338f8080-20240426";
function Hd() {
  if (!(typeof __REACT_DEVTOOLS_GLOBAL_HOOK__ > "u" || typeof __REACT_DEVTOOLS_GLOBAL_HOOK__.checkDCE != "function"))
    try {
      __REACT_DEVTOOLS_GLOBAL_HOOK__.checkDCE(Hd);
    } catch (e) {
      console.error(e);
    }
}
Hd(), Hu.exports = $e;
var rh = Hu.exports, Qd, Nu = rh;
Qd = Nu.createRoot, Nu.hydrateRoot;
function tl(e) {
  return e.replace(/\/+$/, "");
}
async function Di(e, t) {
  let n = "";
  try {
    n = await e.text();
  } catch {
    n = "";
  }
  const r = new Error(`${t} failed with ${e.status}${n ? `: ${n.slice(0, 500)}` : ""}`);
  return r.status = e.status, r.body = n, r;
}
function he(e) {
  return e && typeof e == "object" && !Array.isArray(e) ? e : {};
}
function lh(e) {
  const t = he(e), n = [
    t.config,
    t.client,
    t.data,
    t.result,
    t.item,
    t
  ];
  for (const r of n) {
    const l = he(r);
    if (Object.keys(l).length > 0) return l;
  }
  return t;
}
function I(e, t = "") {
  return e == null ? t : String(e);
}
function nl(e, t = 0) {
  const n = Number(e);
  return Number.isFinite(n) ? n : t;
}
function Xr(e, t = !1) {
  return typeof e == "boolean" ? e : typeof e == "number" ? e === 1 : typeof e == "string" ? ["1", "true", "yes", "ja", "on"].includes(e.toLowerCase()) : t;
}
function Ze(e) {
  if (Array.isArray(e)) return e;
  if (typeof e == "string") {
    const t = e.trim();
    if (!t) return [];
    try {
      const n = JSON.parse(t), r = Ze(n);
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
      const l = Ze(t[r]);
      if (l.length > 0) return l;
    }
  }
  return [];
}
function hr(e, t) {
  for (const n of t) {
    const r = Ze(e[n]);
    if (r.length > 0) return r;
  }
  return [];
}
function ih(e) {
  const t = [];
  for (const n of Ze(e)) {
    const r = he(n), l = nl(r.id, NaN), i = I(r.name ?? r.title, "");
    !Number.isFinite(l) || !i || t.push({
      id: l,
      name: i,
      slug: typeof r.slug == "string" ? r.slug : void 0,
      icon: typeof r.icon == "string" ? r.icon : void 0
    });
  }
  return t;
}
function oh(e) {
  const t = he(e), n = I(t.value ?? t.slug ?? (t.id !== void 0 ? String(t.id) : ""), "").trim(), r = I(t.title ?? t.name, "").trim();
  if (!n || !r) return;
  const l = t.numericValue ?? t.numeric_value, i = l != null ? nl(l, NaN) : NaN;
  return {
    value: n,
    title: r,
    text: I(t.text ?? t.description, "").trim() || void 0,
    icon: I(t.icon, "").trim() || void 0,
    imageUrl: I(t.imageUrl ?? t.image_url, "").trim() || void 0,
    numericValue: Number.isFinite(i) ? i : void 0,
    id: typeof t.id == "number" ? t.id : void 0,
    optionId: typeof t.optionId == "number" ? t.optionId : void 0
  };
}
function sh(e) {
  const t = he(e), n = I(t.answerKey ?? t.answer_key, "").trim(), r = I(t.title, "").trim();
  if (!n || !r) return;
  const l = I(t.questionType ?? t.question_type, "generic"), i = I(t.fieldType ?? t.field_type, "").trim(), o = Ze(t.options).map(oh).filter((a) => !!a);
  return {
    id: typeof t.id == "number" ? t.id : void 0,
    answerKey: n,
    title: r,
    helpText: I(t.helpText ?? t.help_text, "").trim() || null,
    questionType: l,
    multiSelect: Xr(t.multiSelect ?? t.multi_select, !1),
    fieldType: i || null,
    required: Xr(t.required, !1),
    dependsOnAnswerKey: I(t.dependsOnAnswerKey ?? t.depends_on_answer_key, "").trim() || null,
    dependsOnValue: I(t.dependsOnValue ?? t.depends_on_value, "").trim() || null,
    options: o
  };
}
function ah(e) {
  const t = [];
  for (const n of Ze(e)) {
    const r = he(n), l = I(r.key ?? r.stepKey ?? r.step_key, "").trim(), i = I(r.label ?? r.title, "").trim();
    !l || !i || t.push({
      id: typeof r.id == "number" ? r.id : void 0,
      key: l,
      label: i,
      type: I(r.type, "questions"),
      questions: Ze(r.questions).map(sh).filter((o) => !!o)
    });
  }
  return t;
}
async function po(e, t) {
  if (!e) {
    const a = new Error("No apiBaseUrl configured");
    throw a.status = 0, a;
  }
  const n = `${tl(e)}/api/tfd-partner-wizard/config/${encodeURIComponent(t)}`, r = await fetch(n, {
    method: "GET",
    credentials: "omit",
    headers: {
      Accept: "application/json"
    }
  });
  if (!r.ok)
    throw await Di(r, "Config request");
  const l = await r.json(), i = lh(l), o = I(i.intro ?? i.introText ?? i.description, "Finde heraus, welches Partner-Modell und welche Tool-Richtung zu dir passt.");
  return {
    clientId: I(i.clientId ?? i.client_id ?? t, t),
    name: I(i.name, ""),
    headline: I(i.headline ?? i.title, "Chiptuning Händler werden"),
    intro: o,
    introText: o,
    primaryColor: I(i.primaryColor ?? i.primary_color, "#E31E24"),
    theme: I(i.theme, "light"),
    toolRequired: Xr(i.toolRequired ?? i.tool_required, !0),
    defaultVehiclePrice: nl(i.defaultVehiclePrice ?? i.default_vehicle_price, 0),
    enabledVehicleCategories: ih(
      hr(i, ["enabledVehicleCategories", "enabled_vehicle_categories", "vehicleCategories", "vehicle_categories", "vehicles"])
    ),
    enabledServices: hr(i, ["enabledServices", "enabled_services", "services"]),
    currentToolOptions: hr(i, ["currentToolOptions", "current_tool_options", "toolOptions", "tool_options", "currentTools", "current_tools"]),
    customLabels: i.customLabels ?? i.custom_labels ?? {},
    labels: uh(i.labels),
    privacyNotice: I(
      i.privacyNotice ?? i.privacy_notice,
      "Mit dem Absenden erklärst du dich einverstanden, dass deine Angaben zur Bearbeitung der Anfrage gespeichert werden und wir dich kontaktieren."
    ),
    toolDisclaimer: I(i.toolDisclaimer ?? i.tool_disclaimer, ""),
    ctaLabel: I(i.ctaLabel ?? i.cta_label, "Anfrage senden"),
    contactTarget: I(i.contactTarget ?? i.contact_target, ""),
    tools: hr(i, ["tools", "toolRecommendations", "tool_recommendations", "recommendedTools"]),
    packages: hr(i, ["packages", "partnerPackages", "partner_packages"]),
    steps: ah(i.steps),
    appointment: hh(i.appointment),
    raw: l
  };
}
function uh(e) {
  const t = {};
  for (const [n, r] of Object.entries(he(e)))
    typeof r == "string" && (t[n] = r);
  return t;
}
async function rl(e, t, n, r, l) {
  var a;
  const i = await fetch(`${tl(e)}/api/tfd-partner-wizard/admin/${t}`, {
    method: n,
    credentials: "same-origin",
    // Abgelaufene Session leitet zum Login um – das darf nicht als Erfolg gelten.
    redirect: "manual",
    headers: {
      Accept: "application/json",
      ...l !== void 0 ? { "Content-Type": "application/json" } : {},
      "X-CSRF-Token": r
    },
    body: l !== void 0 ? JSON.stringify(l) : void 0
  });
  if (i.type === "opaqueredirect" || i.status === 0)
    throw new Error("Speichern fehlgeschlagen: Sitzung abgelaufen. Bitte im Admin neu einloggen und die Seite neu laden.");
  const o = await i.json().catch(() => null);
  if (!i.ok || !(o != null && o.ok)) {
    let u = `Fehlgeschlagen (${i.status})`;
    (a = o == null ? void 0 : o.errors) != null && a.length ? u += `: ${o.errors.map((y) => typeof y == "string" ? y : y.error).join(" · ")}` : o != null && o.error ? u += `: ${o.error}` : i.ok && (u = "Fehlgeschlagen: unerwartete Antwort vom Server.");
    const d = new Error(u);
    throw d.status = i.status, d;
  }
  return o;
}
async function ch(e, t, n, r) {
  await rl(e, `texts/${encodeURIComponent(t)}`, "PATCH", n, { changes: r });
}
async function dh(e, t, n, r) {
  await rl(e, `questions/${encodeURIComponent(t)}`, "POST", n, r);
}
async function fh(e, t, n, r) {
  await rl(e, `questions/${encodeURIComponent(t)}/${r}`, "DELETE", n);
}
async function ph(e, t, n, r, l) {
  await rl(e, `questions/${encodeURIComponent(t)}/${r}/options`, "POST", n, l);
}
async function mh(e, t, n, r) {
  await rl(e, `options/${encodeURIComponent(t)}/${r}`, "DELETE", n);
}
function hh(e) {
  const t = he(e), n = Xr(t.enabled, !1);
  return {
    enabled: n,
    required: n && Xr(t.required, !1),
    title: I(t.title, "Beratungstermin"),
    durationMinutes: nl(t.durationMinutes, 30),
    timezone: I(t.timezone, "Europe/Berlin")
  };
}
async function gh(e, t) {
  if (!e)
    return [];
  const n = `${tl(e)}/api/tfd-partner-wizard/appointments/${encodeURIComponent(t)}`, r = await fetch(n, {
    method: "GET",
    credentials: "omit",
    cache: "no-store",
    headers: {
      Accept: "application/json"
    }
  });
  if (!r.ok)
    throw await Di(r, "Appointment request");
  const l = he(await r.json());
  return Ze(l.days).map((i) => {
    const o = he(i);
    return {
      date: I(o.date),
      weekday: nl(o.weekday),
      slots: Ze(o.slots).map((a) => {
        const u = he(a);
        return {
          start: I(u.start),
          end: I(u.end),
          time: I(u.time)
        };
      }).filter((a) => a.start !== "")
    };
  }).filter((i) => i.date !== "" && i.slots.length > 0);
}
async function vh(e, t) {
  if (!e)
    throw new Error("No apiBaseUrl configured");
  const n = `${tl(e)}/api/tfd-partner-wizard/recommend`, r = await fetch(n, {
    method: "POST",
    credentials: "omit",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json"
    },
    body: JSON.stringify(t)
  });
  if (!r.ok)
    throw await Di(r, "Recommendation request");
  const l = await r.json();
  return _h(l);
}
async function yh(e, t) {
  if (!e)
    throw new Error("No apiBaseUrl configured");
  const n = `${tl(e)}/api/tfd-partner-wizard/leads`, r = await fetch(n, {
    method: "POST",
    credentials: "omit",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json"
    },
    body: JSON.stringify(t)
  });
  if (!r.ok)
    throw await Di(r, "Lead request");
  try {
    return await r.json();
  } catch {
    return null;
  }
}
function kh(e) {
  return Ze(e).map((t) => {
    if (typeof t == "string") return t.trim();
    const n = he(t);
    return I(n.name ?? n.title ?? n.label ?? n.text ?? n.description).trim();
  }).filter(Boolean);
}
function bd(e) {
  const t = he(e), n = I(t.title).trim();
  if (n)
    return {
      uid: typeof t.uid == "number" ? t.uid : void 0,
      title: n,
      sku: typeof t.sku == "string" ? t.sku : void 0,
      price: t.price !== void 0 && t.price !== null ? String(t.price) : void 0,
      specialPrice: t.specialPrice !== void 0 && t.specialPrice !== null ? String(t.specialPrice) : void 0,
      slug: typeof t.slug == "string" ? t.slug : void 0,
      imageUrl: I(t.imageUrl ?? t.image_url).trim() || void 0
    };
}
function xh(e) {
  const t = [];
  return Ze(e).forEach((n) => {
    const r = bd(n);
    r && t.push(r);
  }), t;
}
function wh(e) {
  const t = [];
  return Ze(e).forEach((n, r) => {
    if (typeof n == "string") {
      const u = n.trim();
      u && t.push({ key: `tool-${r}`, name: u });
      return;
    }
    const l = he(n), i = I(l.name ?? l.title ?? l.label).trim();
    if (!i) return;
    const o = bd(l.product), a = I(l.imageUrl ?? l.image_url).trim() || (o == null ? void 0 : o.imageUrl);
    t.push({
      key: I(l.key ?? l.toolKey ?? `tool-${r}`),
      name: i,
      description: I(l.description ?? l.text, "").trim() || void 0,
      level: typeof l.level == "string" ? l.level : void 0,
      imageUrl: a,
      product: o,
      fromCombination: l.fromCombination === !0
    });
  }), t;
}
function _h(e) {
  const t = he(e), n = he(t.recommendation), r = he(t.result), l = Object.keys(n).length > 0 ? n : Object.keys(r).length > 0 ? r : t, i = he(l.package ?? l.recommendedPackage ?? l.packageData ?? l.recommended_package), o = l.packageName ?? l.packageTitle ?? l.package_title ?? l.recommendedPackageTitle ?? l.recommended_package_title ?? l.title ?? l.name ?? i.name ?? i.title ?? i.packageTitle ?? "Partner Empfehlung", a = l.packageKey ?? l.package_key ?? l.key ?? l.slug ?? i.key ?? i.slug ?? I(o).toLowerCase().replace(/[^a-z0-9]+/gi, "-"), u = kh(
    l.bullets ?? l.items ?? l.benefits ?? l.packageFeatures ?? l.package_features ?? l.features ?? i.bullets ?? i.benefits ?? i.features ?? []
  ), d = wh(
    l.recommendedTools ?? l.recommended_tools ?? l.tools ?? l.toolRecommendations ?? l.tool_recommendations ?? []
  ), y = xh(
    l.recommendedProducts ?? l.recommended_products ?? []
  ), h = I(l.status ?? l.recommendationStatus ?? l.recommendation_status, "ready") === "tool_required" || l.toolRequired === !0 ? "tool_required" : "ready";
  return {
    status: h,
    packageKey: I(a),
    packageName: I(o),
    leadStatus: h === "tool_required" ? "tool_consulting" : "new_partner_lead",
    cta: I(
      l.cta ?? l.ctaLabel ?? l.cta_label ?? l.nextStep ?? l.next_step ?? i.ctaLabel,
      h === "tool_required" ? "Tool-Beratung anfragen" : "Anfrage senden"
    ),
    summary: I(
      l.summary ?? l.packageText ?? l.package_text ?? l.shortDescription ?? l.description ?? i.shortDescription ?? i.description,
      "Auf Basis deiner Angaben bereiten wir die passende Empfehlung vor."
    ),
    bullets: u,
    recommendedTools: d,
    recommendedProducts: y,
    toolDirection: I(l.toolDirection ?? l.tool_direction, ""),
    disclaimer: I(l.disclaimer ?? l.toolDisclaimer ?? l.tool_disclaimer, ""),
    raw: e
  };
}
const Sh = {
  "header.eyebrow": {
    group: "Kopfbereich",
    label: "Kleine Überschrift über der Headline",
    default: "Händler werden"
  },
  "nav.back": {
    group: "Navigation",
    label: "Button Zurück",
    default: "Zurück"
  },
  "nav.next": {
    group: "Navigation",
    label: "Button Weiter",
    default: "Weiter"
  },
  "nav.sending": {
    group: "Navigation",
    label: "Button während des Sendens",
    default: "Senden..."
  },
  "vehicles.selectAll": {
    group: "Fragen",
    label: "Fahrzeuge: Alle auswählen",
    default: "Alle auswählen"
  },
  "vehicles.deselectAll": {
    group: "Fragen",
    label: "Fahrzeuge: Alle abwählen",
    default: "Alle abwählen"
  },
  "summary.title": {
    group: "Seitenleiste",
    label: "Box „Deine Angaben“: Titel",
    default: "Deine Angaben"
  },
  "summary.tool": {
    group: "Seitenleiste",
    label: "Deine Angaben: Tool",
    default: "Tool:"
  },
  "summary.vehicles": {
    group: "Seitenleiste",
    label: "Deine Angaben: Fahrzeuge",
    default: "Fahrzeuge:"
  },
  "summary.services": {
    group: "Seitenleiste",
    label: "Deine Angaben: Leistungen",
    default: "Leistungen:"
  },
  "summary.website": {
    group: "Seitenleiste",
    label: "Deine Angaben: Website",
    default: "Website:"
  },
  "promo.websiteTitle": {
    group: "Seitenleiste",
    label: "Box Website-Empfehlung: Titel",
    default: "Website-Empfehlung"
  },
  "selected.title": {
    group: "Seitenleiste",
    label: "Box „Ausgewählte Produkte“: Titel",
    default: "Ausgewählte Produkte"
  },
  "selected.positionSingular": {
    group: "Seitenleiste",
    label: "Ausgewählte Produkte: Einzahl",
    default: "Position"
  },
  "selected.positionPlural": {
    group: "Seitenleiste",
    label: "Ausgewählte Produkte: Mehrzahl",
    default: "Positionen"
  },
  "selected.total": {
    group: "Seitenleiste",
    label: "Ausgewählte Produkte: Summe",
    default: "Summe:"
  },
  "selected.empty": {
    group: "Seitenleiste",
    label: "Ausgewählte Produkte: leer (Empfehlungs-Schritt)",
    default: "Wähle ein oder mehrere passende Tools aus. Die Auswahl wird mit deiner Anfrage übergeben.",
    multiline: !0
  },
  "selected.emptyContact": {
    group: "Seitenleiste",
    label: "Ausgewählte Produkte: leer (Kontakt-Schritt)",
    default: "Keine Produkte ausgewählt. Du kannst im vorherigen Schritt passende Tools auswählen.",
    multiline: !0
  },
  "product.details": {
    group: "Seitenleiste",
    label: "Button Details",
    default: "Details"
  },
  "toolAd.eyebrow": {
    group: "Seitenleiste",
    label: "Tool-Box: kleine Überschrift",
    default: "Tool Werbung"
  },
  "toolAd.title": {
    group: "Seitenleiste",
    label: "Tool-Box: Titel",
    default: "Passendes Leseschreibtool"
  },
  "toolAd.text": {
    group: "Seitenleiste",
    label: "Tool-Box: Text",
    default: "Für den Händlerstart brauchst du ein unterstütztes Tool. Wir prüfen Protokolle und Fahrzeugbereiche.",
    multiline: !0
  },
  "toolAd.needAnswers": {
    group: "Seitenleiste",
    label: "Tool-Box: Hinweis ohne Antworten",
    default: "Beantworte Fahrzeuge und Tool-Status, um passende Tools zu sehen.",
    multiline: !0
  },
  "toolAd.loading": {
    group: "Seitenleiste",
    label: "Tool-Box: lädt",
    default: "Empfehlung wird aus dem Backend geladen..."
  },
  "toolAd.matchBadge": {
    group: "Seitenleiste",
    label: "Tool-Box: Badge passend",
    default: "Passt zu deiner Auswahl"
  },
  "toolAd.empty": {
    group: "Seitenleiste",
    label: "Tool-Box: keine Empfehlung",
    default: "Für diese Fahrzeugauswahl ist noch keine passende Tool-Empfehlung hinterlegt.",
    multiline: !0
  },
  "product.view": {
    group: "Seitenleiste",
    label: "Button Produkt ansehen",
    default: "Produkt ansehen"
  },
  "price.disclaimer": {
    group: "Allgemein",
    label: "Preis-Hinweis",
    default: "* Preise netto, zzgl. gesetzl. MwSt."
  },
  "recommendation.eyebrow": {
    group: "Empfehlung",
    label: "Kleine Überschrift",
    default: "Deine Empfehlung"
  },
  "recommendation.toolsTitle": {
    group: "Empfehlung",
    label: "Bereich Passende Tools",
    default: "Passende Tools"
  },
  "recommendation.loading": {
    group: "Empfehlung",
    label: "Lädt",
    default: "Wird geladen..."
  },
  "recommendation.select": {
    group: "Empfehlung",
    label: "Button Auswählen",
    default: "Auswählen"
  },
  "recommendation.selected": {
    group: "Empfehlung",
    label: "Button Ausgewählt",
    default: "Ausgewählt"
  },
  "recommendation.exactMatch": {
    group: "Empfehlung",
    label: "Hinweis exakt passend",
    default: "Passt exakt zu deiner Auswahl"
  },
  "recommendation.noTools": {
    group: "Empfehlung",
    label: "Keine Tool-Empfehlung",
    default: "Für diese Auswahl ist noch keine passende Tool-Empfehlung hinterlegt.",
    multiline: !0
  },
  "recommendation.productsTitle": {
    group: "Empfehlung",
    label: "Bereich Produktempfehlungen",
    default: "Produktempfehlungen"
  },
  "recommendation.extrasTitle": {
    group: "Empfehlung",
    label: "Bereich Mit drin",
    default: "Mit drin"
  },
  "recommendation.extraApi": {
    group: "Empfehlung",
    label: "Mit drin: fester Eintrag (leer = ausblenden)",
    default: "Fahrzeugdatenbank API für 1 Jahr mit drin"
  },
  "modal.eyebrow": {
    group: "Produkt-Popup",
    label: "Kleine Überschrift",
    default: "Produktdetails"
  },
  "modal.sku": {
    group: "Produkt-Popup",
    label: "SKU",
    default: "SKU"
  },
  "modal.level": {
    group: "Produkt-Popup",
    label: "Tool-Level",
    default: "Tool-Level"
  },
  "modal.match": {
    group: "Produkt-Popup",
    label: "Empfehlung",
    default: "Empfehlung"
  },
  "modal.exact": {
    group: "Produkt-Popup",
    label: "Exakt passend",
    default: "Exakt passend"
  },
  "modal.fit": {
    group: "Produkt-Popup",
    label: "Passend",
    default: "Passend"
  },
  "modal.add": {
    group: "Produkt-Popup",
    label: "Button hinzufügen",
    default: "Zur Auswahl hinzufügen"
  },
  "modal.remove": {
    group: "Produkt-Popup",
    label: "Button entfernen",
    default: "Aus Auswahl entfernen"
  },
  "contact.title": {
    group: "Kontakt",
    label: "Überschrift",
    default: "Kontakt aufnehmen"
  },
  "contact.lead": {
    group: "Kontakt",
    label: "Einleitung",
    default: "Sende uns deine Angaben. Wir prüfen dein Partner-Modell und melden uns mit der passenden Empfehlung.",
    multiline: !0
  },
  "contact.selectPlaceholder": {
    group: "Kontakt",
    label: "Auswahlfeld: Platzhalter",
    default: "Bitte wählen"
  },
  "appointment.choose": {
    group: "Termin",
    label: "Überschrift ({title} = Termin-Titel)",
    default: "{title} wählen"
  },
  "appointment.optional": {
    group: "Termin",
    label: "Zusatz optional",
    default: "(optional)"
  },
  "appointment.meta": {
    group: "Termin",
    label: "Dauer/Zeitzone ({duration}, {timezone})",
    default: "{duration} Minuten · Zeiten in {timezone}"
  },
  "appointment.booked": {
    group: "Termin",
    label: "Gebucht ({date})",
    default: "Gebucht: {date} Uhr"
  },
  "appointment.selected": {
    group: "Termin",
    label: "Gewählt ({date})",
    default: "Gewählt: {date} Uhr"
  },
  "appointment.loading": {
    group: "Termin",
    label: "Termine laden",
    default: "Freie Termine werden geladen …"
  },
  "appointment.loadError": {
    group: "Termin",
    label: "Fehler beim Laden",
    default: "Freie Termine konnten nicht geladen werden."
  },
  "appointment.empty": {
    group: "Termin",
    label: "Keine Termine frei",
    default: "Aktuell sind keine freien Termine verfügbar. Wir melden uns bei dir.",
    multiline: !0
  },
  "send.success": {
    group: "Meldungen",
    label: "Gesendet",
    default: "Anfrage wurde gespeichert. Wir melden uns mit der passenden Empfehlung.",
    multiline: !0
  },
  "send.successAppointment": {
    group: "Meldungen",
    label: "Gesendet mit Termin ({date})",
    default: "Anfrage wurde gespeichert. Dein Termin am {date} Uhr ist eingetragen.",
    multiline: !0
  },
  "send.slotTaken": {
    group: "Meldungen",
    label: "Termin inzwischen vergeben",
    default: "Der gewählte Termin ist leider gerade vergeben worden. Bitte wähle einen anderen Termin.",
    multiline: !0
  },
  "send.error": {
    group: "Meldungen",
    label: "Senden fehlgeschlagen ({message})",
    default: "Anfrage konnte nicht gesendet werden: {message}",
    multiline: !0
  }
}, Gn = Sh, Cu = Object.fromEntries(
  Object.entries(Gn).map(([e, t]) => [e, t.default])
);
function Gd(e, t) {
  return t ? e.replace(/\{(\w+)\}/g, (n, r) => r in t ? String(t[r]) : n) : e;
}
function Xn(e) {
  return e.target === "label" ? `label:${e.key}` : e.target === "client" ? `client:${e.field}` : `${e.target}:${e.id}:${e.field}`;
}
const Xd = P.createContext({
  enabled: !1,
  t: (e, t) => {
    var n;
    return Gd(((n = Gn[e]) == null ? void 0 : n.default) ?? e, t);
  },
  raw: (e) => {
    var t;
    return ((t = Gn[e]) == null ? void 0 : t.default) ?? e;
  },
  pending: {},
  open: () => {
  }
}), jh = Xd.Provider;
function na() {
  return P.useContext(Xd);
}
function Yd(e, t, n) {
  return {
    className: ["tfdEdit__text", t ? "tfdEdit__text--pending" : "", ""].filter(Boolean).join(" "),
    role: "button",
    tabIndex: 0,
    title: "Klicken zum Bearbeiten",
    onClick: (r) => {
      r.preventDefault(), r.stopPropagation(), e();
    },
    onKeyDown: (r) => {
      (r.key === "Enter" || r.key === " ") && (r.preventDefault(), r.stopPropagation(), e());
    }
  };
}
function F({ k: e, vars: t }) {
  const n = na(), r = n.t(e, t);
  if (!n.enabled) return /* @__PURE__ */ s.jsx(s.Fragment, { children: r });
  const l = Gn[e], i = Xn({ target: "label", key: e }) in n.pending;
  return /* @__PURE__ */ s.jsx(
    "span",
    {
      ...Yd(() => n.open({
        target: { target: "label", key: e },
        title: l ? `${l.group}: ${l.label}` : e,
        current: n.raw(e),
        defaultValue: l == null ? void 0 : l.default,
        multiline: l == null ? void 0 : l.multiline,
        nullable: !0
      }), i),
      children: r || /* @__PURE__ */ s.jsx("em", { className: "tfdEdit__empty", children: "(leer)" })
    }
  );
}
function se({
  target: e,
  value: t,
  title: n,
  multiline: r,
  nullable: l
}) {
  const i = na();
  if (!i.enabled || !e) return /* @__PURE__ */ s.jsx(s.Fragment, { children: t ?? "" });
  const o = i.pending[Xn(e)], a = o ? o.value : t;
  return /* @__PURE__ */ s.jsx(
    "span",
    {
      ...Yd(() => i.open({
        target: e,
        title: n,
        current: a ?? "",
        multiline: r,
        nullable: l
      }), !!o),
      children: a || /* @__PURE__ */ s.jsx("em", { className: "tfdEdit__empty", children: "(leer – klicken zum Ausfüllen)" })
    }
  );
}
function Eh({
  request: e,
  onApply: t,
  onClose: n
}) {
  const [r, l] = P.useState(e.current), i = P.useRef(null), o = e.multiline || e.current.length > 80 || e.current.includes(`
`), a = r.trim() === "", u = e.nullable || !a;
  P.useEffect(() => {
    var g, h;
    (g = i.current) == null || g.focus(), (h = i.current) == null || h.select();
  }, []);
  const d = () => {
    u && t(a ? e.target.target === "label" ? "" : null : r);
  }, y = (g) => {
    g.key === "Escape" ? (g.stopPropagation(), n()) : g.key === "Enter" && (!o || g.metaKey || g.ctrlKey) && (g.preventDefault(), d());
  };
  return /* @__PURE__ */ s.jsx("div", { className: "tfdEdit__backdrop", role: "presentation", onClick: n, children: /* @__PURE__ */ s.jsxs("div", { className: "tfdEdit__dialog", role: "dialog", "aria-modal": "true", "aria-label": "Text bearbeiten", onClick: (g) => g.stopPropagation(), children: [
    /* @__PURE__ */ s.jsx("span", { className: "tfdEdit__dialogTitle", children: e.title }),
    o ? /* @__PURE__ */ s.jsx(
      "textarea",
      {
        ref: (g) => {
          i.current = g;
        },
        value: r,
        rows: 5,
        onChange: (g) => l(g.target.value),
        onKeyDown: y
      }
    ) : /* @__PURE__ */ s.jsx(
      "input",
      {
        ref: (g) => {
          i.current = g;
        },
        type: "text",
        value: r,
        onChange: (g) => l(g.target.value),
        onKeyDown: y
      }
    ),
    e.defaultValue !== void 0 && /* @__PURE__ */ s.jsxs("small", { className: "tfdEdit__hint", children: [
      "Standard: ",
      e.defaultValue || "(leer)"
    ] }),
    /\{\w+\}/.test(e.defaultValue ?? e.current) && /* @__PURE__ */ s.jsxs("small", { className: "tfdEdit__hint", children: [
      "Platzhalter in geschweiften Klammern (z. B. ",
      "{date}",
      ") werden automatisch ersetzt."
    ] }),
    !u && /* @__PURE__ */ s.jsx("small", { className: "tfdEdit__hint tfdEdit__hint--error", children: "Dieses Feld darf nicht leer sein." }),
    /* @__PURE__ */ s.jsxs("div", { className: "tfdEdit__dialogActions", children: [
      e.defaultValue !== void 0 && /* @__PURE__ */ s.jsx("button", { type: "button", onClick: () => t(null), children: "Standard" }),
      /* @__PURE__ */ s.jsx("span", {}),
      /* @__PURE__ */ s.jsx("button", { type: "button", onClick: n, children: "Abbrechen" }),
      /* @__PURE__ */ s.jsx("button", { type: "button", className: "tfdEdit__primary", onClick: d, disabled: !u, children: "Übernehmen" })
    ] })
  ] }) });
}
function Th({ clientFields: e, onClose: t }) {
  var l;
  const n = na(), r = {};
  for (const [i, o] of Object.entries(Gn))
    (r[l = o.group] ?? (r[l] = [])).push(i);
  return /* @__PURE__ */ s.jsxs("div", { className: "tfdEdit__panel", role: "dialog", "aria-label": "Alle Texte", children: [
    /* @__PURE__ */ s.jsxs("div", { className: "tfdEdit__panelHead", children: [
      /* @__PURE__ */ s.jsx("b", { children: "Alle Texte" }),
      /* @__PURE__ */ s.jsx("button", { type: "button", onClick: t, "aria-label": "Schließen", children: "×" })
    ] }),
    /* @__PURE__ */ s.jsxs("div", { className: "tfdEdit__panelBody", children: [
      /* @__PURE__ */ s.jsxs("section", { children: [
        /* @__PURE__ */ s.jsx("h4", { children: "Client" }),
        e.map((i) => {
          const o = n.pending[Xn({ target: "client", field: i.field })], a = o ? o.value ?? "" : i.value;
          return /* @__PURE__ */ s.jsxs(
            "button",
            {
              type: "button",
              className: o ? "tfdEdit__row tfdEdit__row--pending" : "tfdEdit__row",
              onClick: () => n.open({
                target: { target: "client", field: i.field },
                title: `Client: ${i.label}`,
                current: a,
                multiline: i.field !== "headline"
              }),
              children: [
                /* @__PURE__ */ s.jsx("small", { children: i.label }),
                /* @__PURE__ */ s.jsx("span", { children: a || "(leer)" })
              ]
            },
            i.field
          );
        })
      ] }),
      Object.entries(r).map(([i, o]) => /* @__PURE__ */ s.jsxs("section", { children: [
        /* @__PURE__ */ s.jsx("h4", { children: i }),
        o.map((a) => {
          const u = Gn[a], d = Xn({ target: "label", key: a }) in n.pending, y = n.raw(a);
          return /* @__PURE__ */ s.jsxs(
            "button",
            {
              type: "button",
              className: d ? "tfdEdit__row tfdEdit__row--pending" : "tfdEdit__row",
              onClick: () => n.open({
                target: { target: "label", key: a },
                title: `${u.group}: ${u.label}`,
                current: y,
                defaultValue: u.default,
                multiline: u.multiline,
                nullable: !0
              }),
              children: [
                /* @__PURE__ */ s.jsxs("small", { children: [
                  u.label,
                  y !== u.default ? " · angepasst" : ""
                ] }),
                /* @__PURE__ */ s.jsx("span", { children: y || "(leer)" })
              ]
            },
            a
          );
        })
      ] }, i))
    ] })
  ] });
}
const zh = { ä: "ae", ö: "oe", ü: "ue", ß: "ss", Ä: "Ae", Ö: "Oe", Ü: "Ue" };
function Zd(e) {
  return e.replace(/[äöüßÄÖÜ]/g, (t) => zh[t] ?? t).normalize("NFKD").replace(/[̀-ͯ]/g, "").split(/[^A-Za-z0-9]+/).filter(Boolean);
}
function Jd(e, t, n) {
  let r = e.slice(0, n);
  for (let l = 2; t.has(r); l += 1) {
    const i = `_${l}`;
    r = e.slice(0, n - i.length) + i;
  }
  return r;
}
function Nh(e, t) {
  let r = Zd(e).slice(0, 5).map((l) => l.toLowerCase()).map((l, i) => i === 0 ? l : l.charAt(0).toUpperCase() + l.slice(1)).join("");
  return /^[a-z]/i.test(r) || (r = `frage${r}`), Jd(r, t, 40);
}
function Pu(e, t) {
  const n = Zd(e).join("-").toLowerCase() || "option";
  return Jd(n, t, 80);
}
const Ch = [
  { id: "single", label: "Auswahl – eine Antwort", questionType: "generic", fieldType: null, multiSelect: !1, withOptions: !0 },
  { id: "multi", label: "Auswahl – mehrere Antworten", questionType: "generic", fieldType: null, multiSelect: !0, withOptions: !0 }
], Ph = [
  { id: "text", label: "Textfeld (einzeilig)", questionType: "generic", fieldType: "text", multiSelect: !1, withOptions: !1 },
  { id: "textarea", label: "Textfeld (mehrzeilig)", questionType: "generic", fieldType: "textarea", multiSelect: !1, withOptions: !1 },
  { id: "email", label: "E-Mail", questionType: "generic", fieldType: "email", multiSelect: !1, withOptions: !1 },
  { id: "select", label: "Auswahlliste (Dropdown)", questionType: "generic", fieldType: "select", multiSelect: !1, withOptions: !0 },
  { id: "radio", label: "Auswahl – eine Antwort", questionType: "generic", fieldType: "radio", multiSelect: !1, withOptions: !0 },
  { id: "checkboxes", label: "Auswahl – mehrere Antworten", questionType: "generic", fieldType: "checkbox", multiSelect: !0, withOptions: !0 },
  { id: "consent", label: "Einzelne Checkbox (z. B. Zustimmung)", questionType: "generic", fieldType: "checkbox", multiSelect: !1, withOptions: !1 }
];
function Ah({
  stepType: e,
  stepLabel: t,
  busy: n,
  onSubmit: r,
  onClose: l
}) {
  const i = e === "contact" ? Ph : Ch, [o, a] = P.useState(""), [u, d] = P.useState(""), [y, g] = P.useState(i[0].id), [h, k] = P.useState(!1), [E, z] = P.useState(""), Q = P.useRef(null), p = i.find((j) => j.id === y) ?? i[0], c = E.split(`
`).map((j) => j.trim()).filter(Boolean), m = !n && o.trim() !== "" && (!p.withOptions || c.length > 0);
  P.useEffect(() => {
    var j;
    (j = Q.current) == null || j.focus();
  }, []);
  const _ = () => {
    m && r({
      title: o.trim(),
      helpText: u.trim() || null,
      kind: p,
      required: h,
      optionTitles: p.withOptions ? c : []
    });
  };
  return /* @__PURE__ */ s.jsx("div", { className: "tfdEdit__backdrop", role: "presentation", onClick: n ? void 0 : l, children: /* @__PURE__ */ s.jsxs("div", { className: "tfdEdit__dialog", role: "dialog", "aria-modal": "true", "aria-label": "Frage hinzufügen", onClick: (j) => j.stopPropagation(), children: [
    /* @__PURE__ */ s.jsxs("span", { className: "tfdEdit__dialogTitle", children: [
      e === "contact" ? "Neues Kontaktfeld" : "Neue Frage",
      " · ",
      t
    ] }),
    /* @__PURE__ */ s.jsxs("label", { className: "tfdEdit__field", children: [
      /* @__PURE__ */ s.jsx("small", { children: e === "contact" ? "Bezeichnung" : "Frage" }),
      /* @__PURE__ */ s.jsx("input", { ref: Q, type: "text", value: o, maxLength: 180, onChange: (j) => a(j.target.value) })
    ] }),
    e !== "contact" && /* @__PURE__ */ s.jsxs("label", { className: "tfdEdit__field", children: [
      /* @__PURE__ */ s.jsx("small", { children: "Hilfetext (optional)" }),
      /* @__PURE__ */ s.jsx("input", { type: "text", value: u, onChange: (j) => d(j.target.value) })
    ] }),
    /* @__PURE__ */ s.jsxs("label", { className: "tfdEdit__field", children: [
      /* @__PURE__ */ s.jsx("small", { children: "Art" }),
      /* @__PURE__ */ s.jsx("select", { value: y, onChange: (j) => g(j.target.value), children: i.map((j) => /* @__PURE__ */ s.jsx("option", { value: j.id, children: j.label }, j.id)) })
    ] }),
    p.withOptions && /* @__PURE__ */ s.jsxs("label", { className: "tfdEdit__field", children: [
      /* @__PURE__ */ s.jsx("small", { children: "Antworten – eine pro Zeile" }),
      /* @__PURE__ */ s.jsx("textarea", { rows: 5, value: E, onChange: (j) => z(j.target.value), placeholder: `Antwort 1
Antwort 2` })
    ] }),
    /* @__PURE__ */ s.jsxs("label", { className: "tfdEdit__check", children: [
      /* @__PURE__ */ s.jsx("input", { type: "checkbox", checked: h, onChange: (j) => k(j.target.checked) }),
      /* @__PURE__ */ s.jsx("span", { children: "Pflichtfeld" })
    ] }),
    /* @__PURE__ */ s.jsxs("div", { className: "tfdEdit__dialogActions", children: [
      /* @__PURE__ */ s.jsx("span", {}),
      /* @__PURE__ */ s.jsx("button", { type: "button", onClick: l, disabled: n, children: "Abbrechen" }),
      /* @__PURE__ */ s.jsx("button", { type: "button", className: "tfdEdit__primary", onClick: _, disabled: !m, children: n ? "Speichert…" : "Hinzufügen" })
    ] })
  ] }) });
}
function Lh({
  questionTitle: e,
  withText: t,
  busy: n,
  onSubmit: r,
  onClose: l
}) {
  const [i, o] = P.useState(""), [a, u] = P.useState(""), d = P.useRef(null), y = !n && i.trim() !== "";
  P.useEffect(() => {
    var h;
    (h = d.current) == null || h.focus();
  }, []);
  const g = () => {
    y && r({ title: i.trim(), text: a.trim() || null });
  };
  return /* @__PURE__ */ s.jsx("div", { className: "tfdEdit__backdrop", role: "presentation", onClick: n ? void 0 : l, children: /* @__PURE__ */ s.jsxs("div", { className: "tfdEdit__dialog", role: "dialog", "aria-modal": "true", "aria-label": "Antwort hinzufügen", onClick: (h) => h.stopPropagation(), children: [
    /* @__PURE__ */ s.jsxs("span", { className: "tfdEdit__dialogTitle", children: [
      "Neue Antwort · ",
      e
    ] }),
    /* @__PURE__ */ s.jsxs("label", { className: "tfdEdit__field", children: [
      /* @__PURE__ */ s.jsx("small", { children: "Titel" }),
      /* @__PURE__ */ s.jsx(
        "input",
        {
          ref: d,
          type: "text",
          value: i,
          maxLength: 140,
          onChange: (h) => o(h.target.value),
          onKeyDown: (h) => {
            h.key === "Enter" && (h.preventDefault(), g());
          }
        }
      )
    ] }),
    t && /* @__PURE__ */ s.jsxs("label", { className: "tfdEdit__field", children: [
      /* @__PURE__ */ s.jsx("small", { children: "Beschreibung (optional)" }),
      /* @__PURE__ */ s.jsx("textarea", { rows: 3, value: a, onChange: (h) => u(h.target.value) })
    ] }),
    /* @__PURE__ */ s.jsxs("div", { className: "tfdEdit__dialogActions", children: [
      /* @__PURE__ */ s.jsx("span", {}),
      /* @__PURE__ */ s.jsx("button", { type: "button", onClick: l, disabled: n, children: "Abbrechen" }),
      /* @__PURE__ */ s.jsx("button", { type: "button", className: "tfdEdit__primary", onClick: g, disabled: !y, children: n ? "Speichert…" : "Hinzufügen" })
    ] })
  ] }) });
}
function bl({ label: e, onDelete: t }) {
  const n = (r) => {
    r.preventDefault(), r.stopPropagation(), t();
  };
  return /* @__PURE__ */ s.jsx(
    "span",
    {
      className: "tfdEdit__delete",
      role: "button",
      tabIndex: 0,
      title: e,
      "aria-label": e,
      onClick: n,
      onKeyDown: (r) => {
        (r.key === "Enter" || r.key === " ") && n(r);
      },
      children: "×"
    }
  );
}
const gr = {
  clientId: "no1",
  headline: "Chiptuning Händler werden",
  intro: "Finde heraus, welches Partner-Modell und welche Tool-Richtung zu dir passt.",
  privacyNotice: "Mit dem Absenden erklärst du dich einverstanden, dass deine Angaben zur Bearbeitung deiner Anfrage gespeichert werden.",
  ctaLabel: "Anfrage senden"
}, Wh = [
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
], Fh = [
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
], Dh = [
  { value: "pkw", title: "PKW", text: "Standard-Fahrzeuge, Performance und Daily Driver." },
  { value: "lkw", title: "LKW", text: "Nutzfahrzeuge, Flotten und Speditionen." },
  { value: "motorrad", title: "Motorrad", text: "Bike-Tuning und Performance-Anwendungen." },
  { value: "agrar", title: "Agrar", text: "Traktoren, Landmaschinen und Eco-Anwendungen." },
  { value: "atv", title: "Quad", text: "ATV, Quad und Side-by-Side." },
  { value: "boat", title: "Boot", text: "Boote und Marine-Anwendungen." },
  { value: "jetski", title: "Jetski", text: "JetSki und Wassersport-Fahrzeuge." }
], qd = [
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
], Oh = [
  { value: "stage1", title: "Stage 1", text: "Leistungsoptimierung für Kundenfahrzeuge." },
  { value: "stage2", title: "Stage 2", text: "Optimierung mit Hardware-Anpassungen." },
  { value: "eco", title: "Eco-Tuning", text: "Verbrauchsoptimierung für Flotten und Gewerbe." },
  { value: "fileService", title: "File Service", text: "Files lesen, prüfen lassen und schreiben." },
  { value: "diagnostic", title: "Diagnose Support", text: "Technische Rückfragen, Fehlerbilder und Fälle." },
  { value: "special", title: "Sonderoptionen", text: "VMax, DSG, Start-Stop und weitere Optionen." }
], ef = [
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
], Ih = [
  { value: "low", title: "1–5 Fahrzeuge / Monat", text: "Kleiner Start oder gelegentliche Anfragen." },
  { value: "medium", title: "6–20 Fahrzeuge / Monat", text: "Regelmäßiges Geschäft mit Wachstum." },
  { value: "high", title: "20+ Fahrzeuge / Monat", text: "Professioneller Betrieb mit Volumen." },
  { value: "network", title: "Händler / Netzwerk", text: "Mehrere Standorte, Slaves oder Partner." }
];
function Mh(e, t) {
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
const Rh = { low: 3, medium: 12, high: 25, network: 40 }, Bh = [
  {
    key: "entry",
    label: "Einstieg",
    type: "questions",
    questions: [
      { answerKey: "goal", title: "Was ist dein Ziel?", questionType: "generic", multiSelect: !1, options: Wh },
      { answerKey: "experience", title: "Wie viel Erfahrung hast du?", questionType: "generic", multiSelect: !1, options: Fh }
    ]
  },
  {
    key: "vehicleTool",
    label: "Fahrzeug & Tool",
    type: "questions",
    questions: [
      { answerKey: "vehicles", title: "Welche Fahrzeuge möchtest du bearbeiten?", questionType: "vehicleType", multiSelect: !0, options: Dh },
      {
        answerKey: "tool",
        title: "Hast du bereits ein eigenes Tool?",
        questionType: "toolManufacturer",
        multiSelect: !1,
        helpText: "Ohne eigenes Tuning-Tool ist keine direkte Händler-Aktivierung möglich. Dann geht es zuerst in die Tool-Beratung.",
        options: qd
      }
    ]
  },
  {
    key: "offerVolume",
    label: "Angebot & Volumen",
    type: "questions",
    questions: [
      { answerKey: "services", title: "Welche Leistungen willst du anbieten?", questionType: "generic", multiSelect: !0, options: Oh },
      { answerKey: "website", title: "Welche Website möchtest du anbieten?", questionType: "generic", multiSelect: !1, options: ef },
      {
        answerKey: "volume",
        title: "Wie viele Fahrzeuge erwartest du?",
        questionType: "volume",
        multiSelect: !1,
        options: Ih.map((e) => ({ ...e, numericValue: Rh[e.value] }))
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
function $h(e) {
  return {
    value: e.value,
    title: e.title,
    text: e.text ?? "",
    icon: e.icon,
    imageUrl: e.imageUrl,
    id: e.id,
    optionId: e.optionId,
    numericValue: e.numericValue
  };
}
function Ar(e, t) {
  return e.optionId ? { target: "option", id: e.optionId, field: t } : null;
}
function Ll(e, t) {
  return e.id ? { target: "question", id: e.id, field: t } : null;
}
function pt(e, t, n) {
  return t ? { target: e, id: t, field: n } : null;
}
function Au(e) {
  const t = {};
  for (const n of e ?? []) {
    if (!n || typeof n != "object") continue;
    const r = n;
    typeof r.key == "string" && typeof r.id == "number" && (t[r.key] = r.id);
  }
  return t;
}
function Gl(e, t) {
  return e[t];
}
function mo(e, t, n) {
  return { ...e, [t]: n };
}
function wr(e, t) {
  var n;
  return t === "privacyAccepted" ? e.privacyAccepted : (n = e.contact) == null ? void 0 : n[t];
}
function Wl(e, t) {
  const n = e.dependsOnAnswerKey, r = e.dependsOnValue;
  if (!n || !r) return !0;
  const l = Gl(t, n) ?? wr(t, n);
  return Array.isArray(l) ? l.includes(r) : String(l ?? "") === r;
}
function ho(e, t) {
  const n = e ?? [];
  return n.includes(t) ? n.filter((r) => r !== t) : [...n, t];
}
function Uh(e) {
  var t;
  return !!((t = e.vehicles) != null && t.length && e.tool);
}
function Ae(e) {
  if (!e) return;
  const t = Number(e);
  if (Number.isFinite(t))
    return `${t.toLocaleString("de-DE", { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €*`;
}
function nn(e) {
  const t = Number(e == null ? void 0 : e.specialPrice);
  return Number.isFinite(t) && t > 0;
}
function At(e) {
  return nn(e) ? e == null ? void 0 : e.specialPrice : e == null ? void 0 : e.price;
}
function Kh(e, t) {
  if (!e) return 0;
  const n = t.find((r) => r.value === e);
  return (n == null ? void 0 : n.numericValue) ?? 0;
}
function Vh(e) {
  return !e || e === "none" || e === "other" ? "unknown" : "slave";
}
function Lu(e, t, n, r, l) {
  const i = t.contact ?? {}, o = t.vehicles ?? [], a = o.map((k) => n[k]).filter((k) => typeof k == "number"), u = t.services ?? [], d = t.website ?? "", y = t.tool ?? "", g = Kh(t.volume, r), h = Vh(t.tool);
  return {
    clientId: e,
    contact: i,
    answers: t,
    recommendation: l,
    source: "no1_partner_wizard",
    selectedTools: t.selectedTools ?? [],
    // New Symfony recommendation service fields (VehicleType IDs, not slugs).
    currentTool: y,
    vehicleCategories: a,
    mainVehicleCategory: a[0] ?? "",
    services: u,
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
    tool: y,
    volume: t.volume,
    payload: {
      clientId: e,
      contact: i,
      answers: t,
      recommendation: l,
      currentTool: y,
      vehicleCategories: a,
      mainVehicleCategory: a[0] ?? "",
      services: u,
      website: d,
      monthlyVehicleCount: g,
      desiredToolLevel: h,
      privacyAccepted: t.privacyAccepted === !0,
      source: "no1_partner_wizard"
    }
  };
}
function Wu(e, t) {
  try {
    const n = new Date(e), r = new Intl.DateTimeFormat("de-DE", { weekday: "short", day: "2-digit", month: "2-digit", year: "numeric", timeZone: t }).format(n), l = new Intl.DateTimeFormat("de-DE", { hour: "2-digit", minute: "2-digit", timeZone: t }).format(n);
    return `${r.replace(",", "")} · ${l}`;
  } catch {
    return e;
  }
}
function Hh(e, t) {
  try {
    const n = new Date(e);
    return {
      weekday: new Intl.DateTimeFormat("de-DE", { weekday: "short", timeZone: t }).format(n).replace(".", ""),
      date: new Intl.DateTimeFormat("de-DE", { day: "2-digit", month: "2-digit", timeZone: t }).format(n)
    };
  } catch {
    return { weekday: "", date: e.slice(0, 10) };
  }
}
function Fl() {
  return /* @__PURE__ */ s.jsxs("svg", { viewBox: "0 0 20 20", fill: "none", xmlns: "http://www.w3.org/2000/svg", "aria-hidden": "true", children: [
    /* @__PURE__ */ s.jsx("circle", { cx: "10", cy: "10", r: "9", fill: "currentColor", opacity: "0.15" }),
    /* @__PURE__ */ s.jsx("path", { d: "M6 10.5L8.5 13L14 7", stroke: "currentColor", strokeWidth: "2", strokeLinecap: "round", strokeLinejoin: "round" })
  ] });
}
function Qh() {
  return /* @__PURE__ */ s.jsxs("svg", { viewBox: "0 0 20 20", fill: "none", xmlns: "http://www.w3.org/2000/svg", "aria-hidden": "true", children: [
    /* @__PURE__ */ s.jsx("circle", { cx: "10", cy: "10", r: "9", fill: "currentColor", opacity: "0.15" }),
    /* @__PURE__ */ s.jsx("path", { d: "M7 7L13 13M13 7L7 13", stroke: "currentColor", strokeWidth: "2", strokeLinecap: "round", strokeLinejoin: "round" })
  ] });
}
function bh({
  option: e,
  selected: t,
  onClick: n,
  onDelete: r
}) {
  const l = e.imageUrl ?? e.icon;
  return /* @__PURE__ */ s.jsxs(
    "button",
    {
      type: "button",
      className: [
        "tfdWizard__option",
        l ? "tfdWizard__option--withIcon" : "",
        t ? "selected" : ""
      ].filter(Boolean).join(" "),
      onClick: n,
      "data-testid": `option-${e.value}`,
      children: [
        l && /* @__PURE__ */ s.jsx("img", { className: "tfdWizard__optionIcon", src: l, alt: "", loading: "lazy", "aria-hidden": "true" }),
        /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__optionBody", children: [
          /* @__PURE__ */ s.jsx("strong", { children: /* @__PURE__ */ s.jsx(se, { target: Ar(e, "title"), value: e.title, title: "Antwortoption: Titel" }) }),
          /* @__PURE__ */ s.jsx("span", { children: /* @__PURE__ */ s.jsx(se, { target: Ar(e, "text"), value: e.text, title: "Antwortoption: Text", multiline: !0, nullable: !0 }) })
        ] }),
        r && /* @__PURE__ */ s.jsx(bl, { label: "Antwort löschen", onDelete: r })
      ]
    }
  );
}
function Gh({
  clientId: e = "no1",
  apiBaseUrl: t = "",
  primaryColor: n = "#E31E24",
  theme: r = "light",
  editMode: l = !1,
  editCsrf: i = ""
}) {
  var va, ya, ka;
  const o = !!(l && i), [a, u] = P.useState(gr), [d, y] = P.useState(""), [g, h] = P.useState(0), [k, E] = P.useState({}), [z, Q] = P.useState(!1), [p, c] = P.useState(""), [m, _] = P.useState(null), [j, A] = P.useState(""), [W, O] = P.useState(!1), [U, $] = P.useState(null), [ve, jt] = P.useState([]), [Zt, qn] = P.useState(!1), [Oi, gn] = P.useState(""), [er, N] = P.useState(0), [M, B] = P.useState(""), [b, ee] = P.useState(""), [Jt, dt] = P.useState(""), [tr, it] = P.useState(0), [Ke, ll] = P.useState({}), [qt, il] = P.useState(null), [tf, ra] = P.useState(!1), [ol, la] = P.useState(!1), [ia, Et] = P.useState(""), [Ve, vn] = P.useState(null), [yn, oa] = P.useState(!1), sa = P.useRef(!1), sl = P.useMemo(() => {
    const f = { ...Cu, ...a.labels ?? {} };
    for (const v of Object.values(Ke))
      if (v.target.target === "label") {
        const x = v.target.key;
        f[x] = v.value ?? Cu[x] ?? "";
      }
    return f;
  }, [a.labels, Ke]), Tt = P.useCallback(
    (f, v) => Gd(sl[f] ?? f, v),
    [sl]
  ), aa = P.useCallback((f) => {
    il(f), Et("");
  }, []), nf = P.useMemo(() => ({
    enabled: o,
    t: Tt,
    raw: (f) => sl[f] ?? "",
    pending: Ke,
    open: aa
  }), [o, Tt, sl, Ke, aa]), kn = Object.keys(Ke).length;
  P.useEffect(() => {
    let f = !0;
    return po(t, e).then((v) => {
      f && (u({ ...gr, ...v }), y(""));
    }).catch((v) => {
      if (!f) return;
      u(gr);
      const x = typeof v == "object" && v !== null && "status" in v ? String(v.status ?? "offline") : "offline";
      y(`Config konnte nicht geladen werden (${x}). Lokale Fallback-Konfiguration aktiv.`);
    }), () => {
      f = !1;
    };
  }, [t, e]);
  const Ii = P.useMemo(
    () => Mh(k),
    [k, a]
  ), ft = P.useMemo(
    () => a.steps && a.steps.length > 0 ? a.steps : Bh,
    [a]
  ), zt = P.useMemo(() => {
    const f = {};
    for (const v of ft)
      for (const x of v.questions) {
        const L = x.options.map($h);
        if (x.answerKey === "tool") {
          const D = L.some((R) => R.value === "none");
          f[x.answerKey] = D ? L : [qd[0], ...L];
        } else if (x.answerKey === "website") {
          const D = L.some((R) => R.value === "none");
          f[x.answerKey] = D ? L : [ef[0], ...L];
        } else
          f[x.answerKey] = L;
      }
    return f;
  }, [ft]), xn = zt.vehicles ?? [], rf = zt.services ?? [], lf = zt.website ?? [], of = zt.tool ?? [], ua = zt.volume ?? [], Mi = P.useMemo(
    () => Object.fromEntries(
      xn.filter((f) => typeof f.id == "number").map((f) => [f.value, f.id])
    ),
    [xn]
  );
  P.useEffect(() => {
    if (!Uh(k)) {
      _(null), A("");
      return;
    }
    let f = !0;
    O(!0), A("");
    const v = Lu(e, k, Mi, ua, Ii);
    return vh(t, v).then((x) => {
      f && (_(x), A(""));
    }).catch((x) => {
      if (!f) return;
      _(null);
      const L = x instanceof Error ? x.message : "Recommendation API nicht erreichbar.";
      A(`${L} Lokale Empfehlung aktiv.`);
    }).finally(() => {
      f && O(!1);
    }), () => {
      f = !1;
    };
  }, [k, t, e, Ii, Mi, tr]);
  const we = ft[g], oe = m ?? Ii, ca = P.useMemo(
    () => (oe.recommendedTools ?? []).map((f) => f.key),
    [oe]
  ), sf = Math.round((g + 1) / ft.length * 100);
  P.useEffect(() => {
    if (!U) return;
    const f = (v) => {
      v.key === "Escape" && $(null);
    };
    return window.addEventListener("keydown", f), () => window.removeEventListener("keydown", f);
  }, [U]), P.useEffect(() => {
    E((f) => {
      const v = f.selectedTools ?? [];
      if (v.length === 0) return f;
      const x = v.filter((L) => ca.includes(L));
      return x.length === v.length ? f : { ...f, selectedTools: x };
    });
  }, [ca]);
  const wn = !!((va = a.appointment) != null && va.enabled), al = ((ya = a.appointment) == null ? void 0 : ya.timezone) || "Europe/Berlin";
  P.useEffect(() => {
    if (!wn || we.type !== "contact") return;
    let f = !0;
    return qn(!0), gn(""), gh(t, e).then((v) => {
      f && (jt(v), B((x) => {
        var L;
        return v.some((D) => D.date === x) ? x : ((L = v[0]) == null ? void 0 : L.date) ?? "";
      }), ee((x) => v.some((L) => L.slots.some((D) => D.start === x)) ? x : ""));
    }).catch(() => {
      f && (jt([]), gn("load"));
    }).finally(() => {
      f && qn(!1);
    }), () => {
      f = !1;
    };
  }, [wn, we.type, t, e, er]);
  const af = P.useMemo(() => Au(a.packages), [a.packages]), ul = P.useMemo(() => Au(a.tools), [a.tools]), nr = m != null && m.packageKey ? af[m.packageKey] : void 0;
  P.useEffect(() => {
    var f;
    !o || sa.current || !((f = a.steps) != null && f.length) || (sa.current = !0, E((v) => {
      if (Object.keys(v).length > 0) return v;
      let x = {};
      for (const L of ft)
        if (L.type === "questions")
          for (const D of L.questions) {
            const R = zt[D.answerKey] ?? [];
            R.length !== 0 && (x = mo(
              x,
              D.answerKey,
              D.multiSelect ? R.slice(0, 2).map((G) => G.value) : R[0].value
            ));
          }
      return x;
    }));
  }, [o, a.steps, ft, zt]), P.useEffect(() => {
    if (!o || kn === 0) return;
    const f = (v) => {
      v.preventDefault(), v.returnValue = "";
    };
    return window.addEventListener("beforeunload", f), () => window.removeEventListener("beforeunload", f);
  }, [o, kn]);
  const uf = (f) => {
    if (!qt) return;
    const v = Xn(qt.target);
    if (f !== null && f === qt.current && !(v in Ke)) {
      il(null);
      return;
    }
    ll((x) => ({ ...x, [v]: { target: qt.target, value: f } })), il(null);
  }, cf = async () => {
    if (!(!o || kn === 0 || ol)) {
      la(!0), Et("");
      try {
        const f = Object.values(Ke).map((x) => ({ ...x.target, value: x.value }));
        await ch(t, e, i, f);
        const v = await po(t, e);
        u({ ...gr, ...v }), ll({}), it((x) => x + 1), Et(`${f.length} ${f.length === 1 ? "Änderung" : "Änderungen"} gespeichert.`);
      } catch (f) {
        Et(f instanceof Error ? f.message : "Speichern fehlgeschlagen.");
      } finally {
        la(!1);
      }
    }
  }, cl = async (f, v) => {
    if (!o || yn) return !1;
    oa(!0), Et("");
    try {
      await f();
      const x = await po(t, e);
      return u({ ...gr, ...x }), it((L) => L + 1), Et(v), !0;
    } catch (x) {
      return Et(x instanceof Error ? x.message : "Speichern fehlgeschlagen."), !1;
    } finally {
      oa(!1);
    }
  }, da = (f) => {
    ll((v) => Object.fromEntries(
      Object.entries(v).filter(([x]) => !f.some((L) => x.startsWith(L)))
    ));
  }, df = (f) => {
    if (!f.id) return;
    const v = f.options.filter((D) => D.optionId).length, x = `„${f.title}“ wirklich löschen?${v ? ` Die ${v} Antworten werden mit gelöscht.` : ""}

Bereits eingegangene Anfragen bleiben unverändert.`;
    if (!window.confirm(x)) return;
    const L = f.id;
    cl(() => fh(t, e, i, L), `„${f.title}“ gelöscht.`).then((D) => {
      D && (da([`question:${L}:`, ...f.options.filter((R) => R.optionId).map((R) => `option:${R.optionId}:`)]), E((R) => {
        const G = { ...R };
        return delete G[f.answerKey], G;
      }));
    });
  }, dl = (f) => {
    if (!f.optionId || !window.confirm(`Antwort „${f.title}“ wirklich löschen?`)) return;
    const v = f.optionId;
    cl(() => mh(t, e, i, v), `Antwort „${f.title}“ gelöscht.`).then((x) => {
      x && da([`option:${v}:`]);
    });
  }, ff = (f, v) => {
    if (!f.id) return;
    const x = new Set(ft.flatMap((R) => R.questions.map((G) => G.answerKey))), L = /* @__PURE__ */ new Set(), D = f.id;
    cl(() => dh(t, e, i, {
      stepId: D,
      answerKey: Nh(v.title, x),
      title: v.title,
      helpText: v.helpText,
      questionType: v.kind.questionType,
      fieldType: v.kind.fieldType,
      multiSelect: v.kind.multiSelect,
      required: v.required,
      options: v.optionTitles.map((R) => {
        const G = Pu(R, L);
        return L.add(G), { value: G, title: R };
      })
    }), `„${v.title}“ hinzugefügt.`).then((R) => {
      R && vn(null);
    });
  }, pf = (f, v) => {
    if (!f.id) return;
    const x = f.id, L = Pu(v.title, new Set(f.options.map((D) => D.value)));
    cl(
      () => ph(t, e, i, x, { value: L, title: v.title, ...v.text ? { text: v.text } : {} }),
      `Antwort „${v.title}“ hinzugefügt.`
    ).then((D) => {
      D && vn(null);
    });
  }, fa = (f) => f.questionType === "generic" || f.questionType === "volume", pa = (f, v, x) => !o || !f.id ? null : /* @__PURE__ */ s.jsxs("div", { className: "tfdEdit__structureBar", children: [
    f.dependsOnAnswerKey && /* @__PURE__ */ s.jsxs("small", { children: [
      "nur sichtbar bei „",
      f.dependsOnAnswerKey,
      "“",
      f.dependsOnValue ? ` = ${f.dependsOnValue}` : ""
    ] }),
    x && /* @__PURE__ */ s.jsx("button", { type: "button", onClick: () => vn({ kind: "option", question: f }), disabled: yn, children: "+ Antwort" }),
    /* @__PURE__ */ s.jsxs("button", { type: "button", className: "tfdEdit__danger", onClick: () => df(f), disabled: yn, children: [
      v,
      " löschen"
    ] })
  ] }), ma = (f) => !o || !f.id ? null : /* @__PURE__ */ s.jsxs("button", { type: "button", className: "tfdEdit__addQuestion", onClick: () => vn({ kind: "question", step: f }), disabled: yn, children: [
    "+ ",
    f.type === "contact" ? "Feld hinzufügen" : "Frage hinzufügen"
  ] }), mf = [
    { field: "headline", label: "Headline", value: a.headline },
    { field: "introText", label: "Intro-Text", value: a.intro },
    { field: "toolDisclaimer", label: "Tool-Disclaimer", value: a.toolDisclaimer ?? "" },
    { field: "privacyNotice", label: "DSGVO-Hinweis (Client)", value: a.privacyNotice }
  ], fl = P.useMemo(() => {
    var f;
    return o || we.type === "recommendation" ? !0 : we.type === "contact" ? wn && ((f = a.appointment) != null && f.required) && !b ? !1 : we.questions.filter((v) => Wl(v, k) && v.required).every((v) => {
      const x = wr(k, v.answerKey);
      return v.fieldType === "checkbox" ? v.multiSelect ? Array.isArray(x) && x.length > 0 : x === !0 : !!(x && String(x).trim());
    }) : we.questions.filter((v) => Wl(v, k)).every((v) => {
      const x = Gl(k, v.answerKey);
      return v.multiSelect ? Array.isArray(x) && x.length > 0 : !!x;
    });
  }, [o, k, we, wn, (ka = a.appointment) == null ? void 0 : ka.required, b]), hf = (f, v) => {
    E((x) => mo(x, f, v)), c("");
  }, gf = (f, v) => {
    E((x) => mo(x, f, ho(Gl(x, f), v))), c("");
  }, ha = xn.length > 0 && xn.every((f) => (k.vehicles ?? []).includes(f.value)), vf = () => {
    E((f) => ({
      ...f,
      vehicles: ha ? [] : xn.map((v) => v.value)
    })), c("");
  }, yf = (f, v, x = !1) => /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__grid", children: f.map((L) => {
    const D = Gl(k, v), R = Array.isArray(D) ? D.includes(L.value) : D === L.value;
    return /* @__PURE__ */ s.jsx(
      bh,
      {
        option: L,
        selected: R,
        onDelete: o && L.optionId ? () => dl(L) : void 0,
        onClick: () => {
          x ? gf(v, L.value) : hf(v, L.value);
        }
      },
      L.value
    );
  }) }), kf = () => {
    fl && (h((f) => Math.min(f + 1, ft.length - 1)), c(""));
  }, xf = () => {
    h((f) => Math.max(f - 1, 0)), c("");
  }, pl = (f, v) => {
    E((x) => ({
      ...x,
      contact: {
        ...x.contact,
        [f]: v
      }
    })), c("");
  }, wf = (f, v) => {
    E(f === "privacyAccepted" ? (x) => ({ ...x, privacyAccepted: v }) : (x) => ({
      ...x,
      contact: {
        ...x.contact,
        [f]: v ? "true" : ""
      }
    })), c("");
  }, _f = (f, v) => {
    E((x) => {
      var R;
      const L = (R = x.contact) == null ? void 0 : R[f], D = Array.isArray(L) ? L : [];
      return {
        ...x,
        contact: {
          ...x.contact,
          [f]: ho(D, v)
        }
      };
    }), c("");
  }, ga = (f) => {
    E((v) => ({
      ...v,
      selectedTools: ho(v.selectedTools, f)
    })), c("");
  }, ml = (f) => {
    $(f);
  }, Sf = () => {
    if (!U) return null;
    const f = U.product, v = Ae(At(f)), x = nn(f) ? Ae(f == null ? void 0 : f.price) : void 0, L = U.imageUrl ?? (f == null ? void 0 : f.imageUrl), D = (k.selectedTools ?? []).includes(U.key);
    return /* @__PURE__ */ s.jsx(
      "div",
      {
        className: "tfdWizard__modalBackdrop",
        role: "presentation",
        onClick: () => $(null),
        children: /* @__PURE__ */ s.jsxs(
          "div",
          {
            className: "tfdWizard__productModal",
            role: "dialog",
            "aria-modal": "true",
            "aria-label": (f == null ? void 0 : f.title) ?? U.name,
            onClick: (R) => R.stopPropagation(),
            children: [
              /* @__PURE__ */ s.jsx(
                "button",
                {
                  type: "button",
                  className: "tfdWizard__modalClose",
                  onClick: () => $(null),
                  "aria-label": "Produktansicht schließen",
                  children: "×"
                }
              ),
              /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__productModalMedia", children: L ? /* @__PURE__ */ s.jsx("img", { src: L, alt: (f == null ? void 0 : f.title) ?? U.name }) : /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__productModalPlaceholder", children: "No1" }) }),
              /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__productModalBody", children: [
                /* @__PURE__ */ s.jsx("span", { children: /* @__PURE__ */ s.jsx(F, { k: "modal.eyebrow" }) }),
                /* @__PURE__ */ s.jsx("h3", { children: (f == null ? void 0 : f.title) ?? U.name }),
                v && /* @__PURE__ */ s.jsxs("p", { children: [
                  x && /* @__PURE__ */ s.jsx("s", { className: "tfdWizard__priceOriginal", children: x }),
                  /* @__PURE__ */ s.jsx("strong", { children: v })
                ] }),
                U.description && /* @__PURE__ */ s.jsx("p", { children: U.description }),
                /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__productFacts", children: [
                  (f == null ? void 0 : f.sku) && /* @__PURE__ */ s.jsxs("div", { children: [
                    /* @__PURE__ */ s.jsx("em", { children: /* @__PURE__ */ s.jsx(F, { k: "modal.sku" }) }),
                    /* @__PURE__ */ s.jsx("b", { children: f.sku })
                  ] }),
                  U.level && /* @__PURE__ */ s.jsxs("div", { children: [
                    /* @__PURE__ */ s.jsx("em", { children: /* @__PURE__ */ s.jsx(F, { k: "modal.level" }) }),
                    /* @__PURE__ */ s.jsx("b", { children: U.level })
                  ] }),
                  /* @__PURE__ */ s.jsxs("div", { children: [
                    /* @__PURE__ */ s.jsx("em", { children: /* @__PURE__ */ s.jsx(F, { k: "modal.match" }) }),
                    /* @__PURE__ */ s.jsx("b", { children: /* @__PURE__ */ s.jsx(F, { k: U.fromCombination ? "modal.exact" : "modal.fit" }) })
                  ] })
                ] }),
                /* @__PURE__ */ s.jsx(
                  "button",
                  {
                    type: "button",
                    className: "tfdWizard__modalSelect",
                    onClick: () => ga(U.key),
                    "aria-pressed": D,
                    children: /* @__PURE__ */ s.jsx(F, { k: D ? "modal.remove" : "modal.add" })
                  }
                )
              ] })
            ]
          }
        )
      }
    );
  }, jf = async () => {
    var v;
    if (!fl || z) return;
    if (o) {
      c("Bearbeitungsmodus: Es wird keine Anfrage gesendet.");
      return;
    }
    Q(!0), c("");
    const f = {
      ...Lu(e, k, Mi, ua, oe),
      ...wn && b ? { appointment: { start: b } } : {}
    };
    try {
      const x = await yh(t, f), L = (v = x == null ? void 0 : x.appointment) == null ? void 0 : v.start;
      if (L) {
        const D = Wu(L, al);
        dt(D), ee(""), N((R) => R + 1), c(Tt("send.successAppointment", { date: D }));
      } else
        c(Tt("send.success"));
    } catch (x) {
      if ((typeof x == "object" && x !== null && "status" in x ? x.status : void 0) === 409)
        ee(""), N((D) => D + 1), c(Tt("send.slotTaken"));
      else {
        const D = x instanceof Error ? x.message : "Unbekannter API-Fehler";
        c(Tt("send.error", { message: D }));
      }
    } finally {
      Q(!1);
    }
  }, Ef = () => {
    var Nt, Ct;
    const f = (k.vehicles ?? []).map((T) => xn.find((ne) => ne.value === T)).filter((T) => !!T).slice(0, 4), v = (k.services ?? []).map((T) => rf.find((ne) => ne.value === T)).filter((T) => !!T).slice(0, 4), x = lf.find((T) => T.value === k.website), L = (Nt = of.find((T) => T.value === k.tool)) == null ? void 0 : Nt.title, D = (oe.recommendedTools ?? []).filter((T) => (k.selectedTools ?? []).includes(T.key)), R = D.reduce((T, ne) => {
      const Pe = Number(At(ne.product));
      return Number.isFinite(Pe) ? T + Pe : T;
    }, 0), G = !!((Ct = k.vehicles) != null && Ct.length && k.tool), w = !!(k.tool && k.tool !== "none"), ce = D.length > 0, te = we.type === "recommendation" && ["full_package", "website_only", "configurator_only"].includes(oe.packageKey ?? "");
    return /* @__PURE__ */ s.jsxs("aside", { className: "tfdWizard__promoAside", "aria-label": "Tool Empfehlungen", children: [
      (f.length > 0 || v.length > 0 || x || L) && /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__promoCard tfdWizard__summaryCard", children: [
        /* @__PURE__ */ s.jsx("span", { children: /* @__PURE__ */ s.jsx(F, { k: "summary.title" }) }),
        L && /* @__PURE__ */ s.jsxs("p", { className: "tfdWizard__summaryLine", children: [
          /* @__PURE__ */ s.jsx("b", { children: /* @__PURE__ */ s.jsx(F, { k: "summary.tool" }) }),
          /* @__PURE__ */ s.jsx("span", { className: `tfdWizard__statusIcon ${w ? "tfdWizard__statusIcon--yes" : "tfdWizard__statusIcon--no"}`, children: w ? /* @__PURE__ */ s.jsx(Fl, {}) : /* @__PURE__ */ s.jsx(Qh, {}) }),
          L
        ] }),
        f.length > 0 && /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__summaryLine", children: [
          /* @__PURE__ */ s.jsx("b", { children: /* @__PURE__ */ s.jsx(F, { k: "summary.vehicles" }) }),
          /* @__PURE__ */ s.jsx("span", { className: "tfdWizard__summaryIconList", children: f.map((T) => {
            const ne = T.imageUrl ?? T.icon;
            return /* @__PURE__ */ s.jsxs("span", { className: "tfdWizard__summaryIconItem", title: T.title, children: [
              ne ? /* @__PURE__ */ s.jsx("img", { src: ne, alt: "", loading: "lazy" }) : /* @__PURE__ */ s.jsx("span", { className: "tfdWizard__statusIcon tfdWizard__statusIcon--yes", children: /* @__PURE__ */ s.jsx(Fl, {}) }),
              T.title
            ] }, T.value);
          }) })
        ] }),
        v.length > 0 && /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__summaryLine", children: [
          /* @__PURE__ */ s.jsx("b", { children: /* @__PURE__ */ s.jsx(F, { k: "summary.services" }) }),
          /* @__PURE__ */ s.jsx("span", { className: "tfdWizard__summaryIconList", children: v.map((T) => {
            const ne = T.imageUrl ?? T.icon;
            return /* @__PURE__ */ s.jsxs("span", { className: "tfdWizard__summaryIconItem", title: T.title, children: [
              ne ? /* @__PURE__ */ s.jsx("img", { src: ne, alt: "", loading: "lazy" }) : /* @__PURE__ */ s.jsx("span", { className: "tfdWizard__statusIcon tfdWizard__statusIcon--yes", children: /* @__PURE__ */ s.jsx(Fl, {}) }),
              T.title
            ] }, T.value);
          }) })
        ] }),
        x && /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__summaryLine", children: [
          /* @__PURE__ */ s.jsx("b", { children: /* @__PURE__ */ s.jsx(F, { k: "summary.website" }) }),
          /* @__PURE__ */ s.jsx("span", { className: "tfdWizard__summaryIconList", children: /* @__PURE__ */ s.jsxs("span", { className: "tfdWizard__summaryIconItem", title: x.title, children: [
            /* @__PURE__ */ s.jsx("span", { className: "tfdWizard__statusIcon tfdWizard__statusIcon--yes", children: /* @__PURE__ */ s.jsx(Fl, {}) }),
            x.title
          ] }) })
        ] })
      ] }),
      we.type === "recommendation" && te && /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__promoCard tfdWizard__promoCard--accent", children: [
        /* @__PURE__ */ s.jsx("span", { children: /* @__PURE__ */ s.jsx(F, { k: "promo.websiteTitle" }) }),
        /* @__PURE__ */ s.jsx("strong", { children: /* @__PURE__ */ s.jsx(se, { target: pt("package", nr, "title"), value: oe.packageName, title: "Paket: Titel" }) }),
        /* @__PURE__ */ s.jsx("p", { children: /* @__PURE__ */ s.jsx(se, { target: pt("package", nr, "description"), value: oe.packageText ?? oe.summary, title: "Paket: Beschreibung", multiline: !0 }) })
      ] }),
      we.type === "recommendation" && /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__promoCard tfdWizard__selectedProductsCard", children: [
        /* @__PURE__ */ s.jsx("span", { children: /* @__PURE__ */ s.jsx(F, { k: "selected.title" }) }),
        ce ? /* @__PURE__ */ s.jsxs(s.Fragment, { children: [
          /* @__PURE__ */ s.jsxs("strong", { children: [
            D.length,
            " ",
            /* @__PURE__ */ s.jsx(F, { k: D.length === 1 ? "selected.positionSingular" : "selected.positionPlural" })
          ] }),
          /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__selectedProductsList", children: D.map((T) => {
            var V, rr, lr, ir, or;
            const ne = Ae(At(T.product)), Pe = nn(T.product) ? Ae((V = T.product) == null ? void 0 : V.price) : void 0;
            return /* @__PURE__ */ s.jsxs("article", { children: [
              (T.imageUrl || ((rr = T.product) == null ? void 0 : rr.imageUrl)) && /* @__PURE__ */ s.jsx("img", { src: T.imageUrl ?? ((lr = T.product) == null ? void 0 : lr.imageUrl), alt: ((ir = T.product) == null ? void 0 : ir.title) ?? T.name, loading: "lazy" }),
              /* @__PURE__ */ s.jsxs("div", { children: [
                /* @__PURE__ */ s.jsx("b", { children: ((or = T.product) == null ? void 0 : or.title) ?? T.name }),
                Pe && /* @__PURE__ */ s.jsx("s", { className: "tfdWizard__priceOriginal", children: Pe }),
                ne && /* @__PURE__ */ s.jsx("em", { children: ne }),
                /* @__PURE__ */ s.jsx("button", { type: "button", onClick: () => ml(T), children: /* @__PURE__ */ s.jsx(F, { k: "product.details" }) })
              ] })
            ] }, T.key);
          }) }),
          R > 0 && /* @__PURE__ */ s.jsxs("p", { className: "tfdWizard__selectedProductsTotal", children: [
            /* @__PURE__ */ s.jsx("b", { children: /* @__PURE__ */ s.jsx(F, { k: "selected.total" }) }),
            " ",
            Ae(String(R))
          ] }),
          /* @__PURE__ */ s.jsx("p", { className: "tfdWizard__priceDisclaimer", children: /* @__PURE__ */ s.jsx(F, { k: "price.disclaimer" }) })
        ] }) : /* @__PURE__ */ s.jsx("p", { children: /* @__PURE__ */ s.jsx(F, { k: "selected.empty" }) })
      ] }),
      !w && g < 3 && /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__promoCard tfdWizard__promoCard--accent", children: [
        /* @__PURE__ */ s.jsx("span", { children: /* @__PURE__ */ s.jsx(F, { k: "toolAd.eyebrow" }) }),
        /* @__PURE__ */ s.jsx("strong", { children: /* @__PURE__ */ s.jsx(F, { k: "toolAd.title" }) }),
        /* @__PURE__ */ s.jsx("p", { children: /* @__PURE__ */ s.jsx(F, { k: "toolAd.text" }) }),
        G && j && /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__miniNotice", children: j }),
        G ? W ? /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__miniNotice", children: /* @__PURE__ */ s.jsx(F, { k: "toolAd.loading" }) }) : oe.recommendedTools && oe.recommendedTools.length > 0 ? /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__adList", children: oe.recommendedTools.slice(0, 4).map((T) => {
          var V;
          const ne = Ae(At(T.product)), Pe = nn(T.product) ? Ae((V = T.product) == null ? void 0 : V.price) : void 0;
          return /* @__PURE__ */ s.jsxs("article", { className: T.imageUrl ? "tfdWizard__adItem--withImage" : void 0, children: [
            T.imageUrl && /* @__PURE__ */ s.jsx("img", { className: "tfdWizard__adImage", src: T.imageUrl, alt: T.name, loading: "lazy" }),
            /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__adBody", children: [
              /* @__PURE__ */ s.jsx("b", { children: /* @__PURE__ */ s.jsx(se, { target: pt("tool", ul[T.key], "name"), value: T.name, title: "Tool: Name" }) }),
              T.fromCombination && /* @__PURE__ */ s.jsx("span", { className: "tfdWizard__adBadge", children: /* @__PURE__ */ s.jsx(F, { k: "toolAd.matchBadge" }) }),
              T.description && /* @__PURE__ */ s.jsx("small", { children: /* @__PURE__ */ s.jsx(se, { target: pt("tool", ul[T.key], "description"), value: T.description, title: "Tool: Beschreibung", multiline: !0, nullable: !0 }) }),
              T.product && /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__adProduct", children: [
                Pe && /* @__PURE__ */ s.jsx("s", { className: "tfdWizard__priceOriginal", children: Pe }),
                ne && /* @__PURE__ */ s.jsx("span", { className: "tfdWizard__adPrice", children: ne }),
                /* @__PURE__ */ s.jsx(
                  "button",
                  {
                    type: "button",
                    className: "tfdWizard__adLink",
                    onClick: () => ml(T),
                    children: /* @__PURE__ */ s.jsx(F, { k: "product.view" })
                  }
                )
              ] })
            ] })
          ] }, T.key);
        }) }) : G ? /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__miniNotice", children: /* @__PURE__ */ s.jsx(F, { k: "toolAd.empty" }) }) : /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__tagList", children: [
          /* @__PURE__ */ s.jsx("em", { children: "Autotuner" }),
          /* @__PURE__ */ s.jsx("em", { children: "KESS3" }),
          /* @__PURE__ */ s.jsx("em", { children: "FLEX" })
        ] }) : /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__miniNotice", children: /* @__PURE__ */ s.jsx(F, { k: "toolAd.needAnswers" }) }),
        oe.recommendedTools && oe.recommendedTools.length > 0 && /* @__PURE__ */ s.jsx("p", { className: "tfdWizard__priceDisclaimer", children: /* @__PURE__ */ s.jsx(F, { k: "price.disclaimer" }) })
      ] }),
      a.toolDisclaimer && we.key === "vehicleTool" && /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__miniNotice", children: /* @__PURE__ */ s.jsx(se, { target: { target: "client", field: "toolDisclaimer" }, value: a.toolDisclaimer, title: "Client: Tool-Disclaimer", multiline: !0 }) }),
      we.type === "contact" && /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__promoCard tfdWizard__selectedProductsCard", children: [
        /* @__PURE__ */ s.jsx("span", { children: /* @__PURE__ */ s.jsx(F, { k: "selected.title" }) }),
        ce ? /* @__PURE__ */ s.jsxs(s.Fragment, { children: [
          /* @__PURE__ */ s.jsxs("strong", { children: [
            D.length,
            " ",
            /* @__PURE__ */ s.jsx(F, { k: D.length === 1 ? "selected.positionSingular" : "selected.positionPlural" })
          ] }),
          /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__selectedProductsList", children: D.map((T) => {
            var V, rr, lr, ir, or;
            const ne = Ae(At(T.product)), Pe = nn(T.product) ? Ae((V = T.product) == null ? void 0 : V.price) : void 0;
            return /* @__PURE__ */ s.jsxs("article", { children: [
              (T.imageUrl || ((rr = T.product) == null ? void 0 : rr.imageUrl)) && /* @__PURE__ */ s.jsx("img", { src: T.imageUrl ?? ((lr = T.product) == null ? void 0 : lr.imageUrl), alt: ((ir = T.product) == null ? void 0 : ir.title) ?? T.name, loading: "lazy" }),
              /* @__PURE__ */ s.jsxs("div", { children: [
                /* @__PURE__ */ s.jsx("b", { children: ((or = T.product) == null ? void 0 : or.title) ?? T.name }),
                Pe && /* @__PURE__ */ s.jsx("s", { className: "tfdWizard__priceOriginal", children: Pe }),
                ne && /* @__PURE__ */ s.jsx("em", { children: ne }),
                /* @__PURE__ */ s.jsx("button", { type: "button", onClick: () => ml(T), children: /* @__PURE__ */ s.jsx(F, { k: "product.details" }) })
              ] })
            ] }, T.key);
          }) }),
          R > 0 && /* @__PURE__ */ s.jsxs("p", { className: "tfdWizard__selectedProductsTotal", children: [
            /* @__PURE__ */ s.jsx("b", { children: /* @__PURE__ */ s.jsx(F, { k: "selected.total" }) }),
            " ",
            Ae(String(R))
          ] }),
          /* @__PURE__ */ s.jsx("p", { className: "tfdWizard__priceDisclaimer", children: /* @__PURE__ */ s.jsx(F, { k: "price.disclaimer" }) })
        ] }) : /* @__PURE__ */ s.jsx("p", { children: /* @__PURE__ */ s.jsx(F, { k: "selected.emptyContact" }) })
      ] })
    ] });
  }, Tf = (f) => /* @__PURE__ */ s.jsxs(s.Fragment, { children: [
    f.questions.filter((v) => o || Wl(v, k)).map((v) => /* @__PURE__ */ s.jsxs("section", { className: "tfdWizard__block", children: [
      /* @__PURE__ */ s.jsx("h3", { className: "tfdWizard__title", children: /* @__PURE__ */ s.jsx(se, { target: Ll(v, "title"), value: v.title, title: "Frage: Titel" }) }),
      pa(v, "Frage", fa(v)),
      v.questionType === "vehicleType" && /* @__PURE__ */ s.jsx(
        "button",
        {
          type: "button",
          className: "tfdWizard__selectAllButton",
          onClick: vf,
          "data-testid": "vehicles-select-all-button",
          children: /* @__PURE__ */ s.jsx(F, { k: ha ? "vehicles.deselectAll" : "vehicles.selectAll" })
        }
      ),
      (v.helpText || o && v.id) && /* @__PURE__ */ s.jsx("p", { className: "tfdWizard__lead", children: /* @__PURE__ */ s.jsx(se, { target: Ll(v, "helpText"), value: v.helpText, title: "Frage: Hilfetext", multiline: !0, nullable: !0 }) }),
      yf(zt[v.answerKey] ?? [], v.answerKey, v.multiSelect)
    ] }, v.answerKey)),
    ma(f)
  ] }), zf = () => {
    var L, D, R;
    const f = ve.find((G) => G.date === M) ?? ve[0], v = ((L = a.appointment) == null ? void 0 : L.title) || "Beratungstermin", x = ((D = a.appointment) == null ? void 0 : D.durationMinutes) ?? 30;
    return /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__appointment", "data-testid": "appointment-picker", children: [
      /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__appointmentHead", children: [
        /* @__PURE__ */ s.jsxs("span", { className: "tfdWizard__checkboxGroupLabel", children: [
          /* @__PURE__ */ s.jsx(F, { k: "appointment.choose", vars: { title: v } }),
          (R = a.appointment) != null && R.required ? " *" : /* @__PURE__ */ s.jsxs(s.Fragment, { children: [
            " ",
            /* @__PURE__ */ s.jsx(F, { k: "appointment.optional" })
          ] })
        ] }),
        /* @__PURE__ */ s.jsx("small", { children: /* @__PURE__ */ s.jsx(F, { k: "appointment.meta", vars: { duration: x, timezone: al.replace("_", " ") } }) })
      ] }),
      Jt && /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__appointmentBooked", children: /* @__PURE__ */ s.jsx(F, { k: "appointment.booked", vars: { date: Jt } }) }),
      Zt && ve.length === 0 ? /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__appointmentEmpty", children: /* @__PURE__ */ s.jsx(F, { k: "appointment.loading" }) }) : Oi ? /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__appointmentEmpty", children: /* @__PURE__ */ s.jsx(F, { k: "appointment.loadError" }) }) : ve.length === 0 ? /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__appointmentEmpty", children: /* @__PURE__ */ s.jsx(F, { k: "appointment.empty" }) }) : /* @__PURE__ */ s.jsxs(s.Fragment, { children: [
        /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__appointmentDays", role: "tablist", children: ve.map((G) => {
          const w = Hh(G.slots[0].start, al);
          return /* @__PURE__ */ s.jsxs(
            "button",
            {
              type: "button",
              role: "tab",
              "aria-selected": G.date === (f == null ? void 0 : f.date),
              className: G.date === (f == null ? void 0 : f.date) ? "active" : "",
              onClick: () => B(G.date),
              children: [
                /* @__PURE__ */ s.jsx("small", { children: w.weekday }),
                /* @__PURE__ */ s.jsx("b", { children: w.date })
              ]
            },
            G.date
          );
        }) }),
        f && /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__appointmentSlots", children: f.slots.map((G) => {
          const w = G.start === b;
          return /* @__PURE__ */ s.jsx(
            "button",
            {
              type: "button",
              className: w ? "active" : "",
              "aria-pressed": w,
              onClick: () => {
                ee(w ? "" : G.start), c("");
              },
              children: G.time
            },
            G.start
          );
        }) }),
        b && /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__appointmentSelected", children: /* @__PURE__ */ s.jsx("b", { children: /* @__PURE__ */ s.jsx(F, { k: "appointment.selected", vars: { date: Wu(b, al) } }) }) })
      ] })
    ] });
  }, Nf = (f) => {
    const v = (w) => w.fieldType === "checkbox" && !w.multiSelect, x = f.questions.filter((w) => o || Wl(w, k)), L = (w) => ["select", "radio"].includes(w.fieldType ?? "") || w.fieldType === "checkbox" && w.multiSelect, D = (w) => {
      if (!o || !w.id) return null;
      const ce = L(w) && fa(w);
      return /* @__PURE__ */ s.jsxs(s.Fragment, { children: [
        ce && w.fieldType === "select" && /* @__PURE__ */ s.jsxs("div", { className: "tfdEdit__chips", children: [
          /* @__PURE__ */ s.jsx("small", { children: "Antworten:" }),
          w.options.map((te) => /* @__PURE__ */ s.jsxs("span", { className: "tfdEdit__chip", children: [
            /* @__PURE__ */ s.jsx(se, { target: Ar(te, "title"), value: te.title, title: "Antwortoption: Titel" }),
            te.optionId && /* @__PURE__ */ s.jsx(bl, { label: "Antwort löschen", onDelete: () => dl(te) })
          ] }, te.value))
        ] }),
        pa(w, "Feld", ce)
      ] });
    }, R = x.filter((w) => !v(w)), G = x.filter(v);
    return /* @__PURE__ */ s.jsxs("section", { className: "tfdWizard__block", children: [
      /* @__PURE__ */ s.jsx("h3", { className: "tfdWizard__title", children: /* @__PURE__ */ s.jsx(F, { k: "contact.title" }) }),
      /* @__PURE__ */ s.jsx("p", { className: "tfdWizard__lead", children: /* @__PURE__ */ s.jsx(F, { k: "contact.lead" }) }),
      /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__form", children: R.map((w) => {
        const ce = wr(k, w.answerKey), te = typeof ce == "string" ? ce : "", Nt = Array.isArray(ce) ? ce : [], Ct = /* @__PURE__ */ s.jsxs(s.Fragment, { children: [
          /* @__PURE__ */ s.jsx(se, { target: Ll(w, "title"), value: w.title, title: "Kontaktfeld: Bezeichnung" }),
          w.required ? " *" : ""
        ] }), T = `contact-${w.answerKey}-input`, ne = w.fieldType === "radio" || w.fieldType === "checkbox", Pe = w.fieldType === "textarea" || ne;
        return ne ? /* @__PURE__ */ s.jsxs("div", { className: Pe ? "tfdWizard__fieldFull" : void 0, children: [
          /* @__PURE__ */ s.jsx("span", { className: "tfdWizard__checkboxGroupLabel", children: Ct }),
          w.fieldType === "radio" ? /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__radioGroup", "data-testid": T, children: w.options.map((V) => /* @__PURE__ */ s.jsxs("label", { className: "tfdWizard__radioOption", children: [
            /* @__PURE__ */ s.jsx(
              "input",
              {
                type: "radio",
                name: w.answerKey,
                checked: te === V.value,
                onChange: () => pl(w.answerKey, V.value)
              }
            ),
            /* @__PURE__ */ s.jsx("span", { children: /* @__PURE__ */ s.jsx(se, { target: Ar(V, "title"), value: V.title, title: "Antwortoption: Titel" }) }),
            o && V.optionId && /* @__PURE__ */ s.jsx(bl, { label: "Antwort löschen", onDelete: () => dl(V) })
          ] }, V.value)) }) : /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__checkboxGroup", "data-testid": T, children: w.options.map((V) => /* @__PURE__ */ s.jsxs("label", { className: "tfdWizard__checkboxOption", children: [
            /* @__PURE__ */ s.jsx(
              "input",
              {
                type: "checkbox",
                checked: Nt.includes(V.value),
                onChange: () => _f(w.answerKey, V.value)
              }
            ),
            /* @__PURE__ */ s.jsx("span", { children: /* @__PURE__ */ s.jsx(se, { target: Ar(V, "title"), value: V.title, title: "Antwortoption: Titel" }) }),
            o && V.optionId && /* @__PURE__ */ s.jsx(bl, { label: "Antwort löschen", onDelete: () => dl(V) })
          ] }, V.value)) }),
          D(w)
        ] }, w.answerKey) : /* @__PURE__ */ s.jsxs("label", { className: Pe ? "tfdWizard__fieldFull" : void 0, children: [
          /* @__PURE__ */ s.jsx("span", { children: Ct }),
          w.fieldType === "textarea" ? /* @__PURE__ */ s.jsx(
            "textarea",
            {
              value: te,
              onChange: (V) => pl(w.answerKey, V.target.value),
              "data-testid": T
            }
          ) : w.fieldType === "select" ? /* @__PURE__ */ s.jsxs(
            "select",
            {
              value: te,
              onChange: (V) => pl(w.answerKey, V.target.value),
              "data-testid": T,
              children: [
                /* @__PURE__ */ s.jsx("option", { value: "", children: Tt("contact.selectPlaceholder") }),
                w.options.map((V) => /* @__PURE__ */ s.jsx("option", { value: V.value, children: V.title }, V.value))
              ]
            }
          ) : /* @__PURE__ */ s.jsx(
            "input",
            {
              type: w.fieldType === "email" ? "email" : "text",
              value: te,
              onChange: (V) => pl(w.answerKey, V.target.value),
              "data-testid": T
            }
          ),
          D(w)
        ] }, w.answerKey);
      }) }),
      wn && zf(),
      G.map((w) => {
        const ce = wr(k, w.answerKey) === !0 || wr(k, w.answerKey) === "true";
        return /* @__PURE__ */ s.jsxs("label", { className: "tfdWizard__privacy", children: [
          /* @__PURE__ */ s.jsx(
            "input",
            {
              type: "checkbox",
              checked: ce,
              onChange: (te) => wf(w.answerKey, te.target.checked),
              "data-testid": `contact-${w.answerKey}-checkbox`
            }
          ),
          /* @__PURE__ */ s.jsxs("span", { children: [
            /* @__PURE__ */ s.jsx(se, { target: Ll(w, "title"), value: w.title, title: "Checkbox: Text", multiline: !0 }),
            D(w)
          ] })
        ] }, w.answerKey);
      }),
      ma(f),
      p && /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__notice", children: p })
    ] });
  }, Cf = () => {
    var f, v;
    switch (we.type) {
      case "questions":
        return Tf(we);
      case "recommendation": {
        const x = Tt("recommendation.extraApi"), L = Array.from(new Set([...oe.bullets ?? [], x].filter(Boolean))), D = ((f = oe.recommendedTools) == null ? void 0 : f.slice(0, 4)) ?? [], R = k.selectedTools ?? [];
        return D.filter((w) => R.includes(w.key)).reduce((w, ce) => {
          const te = Number(At(ce.product));
          return Number.isFinite(te) ? w + te : w;
        }, 0), /* @__PURE__ */ s.jsxs("section", { className: "tfdWizard__block tfdWizard__recommendationStep", children: [
          /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__recommendationHero", children: [
            /* @__PURE__ */ s.jsx("span", { children: /* @__PURE__ */ s.jsx(F, { k: "recommendation.eyebrow" }) }),
            /* @__PURE__ */ s.jsx("h3", { children: /* @__PURE__ */ s.jsx(se, { target: pt("package", nr, "title"), value: oe.packageName, title: "Paket: Titel" }) }),
            /* @__PURE__ */ s.jsx("p", { children: /* @__PURE__ */ s.jsx(se, { target: pt("package", nr, "description"), value: oe.summary, title: "Paket: Beschreibung", multiline: !0 }) })
          ] }),
          /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__recommendationPanel", children: [
            /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__recommendationPanelHeader", children: [
              /* @__PURE__ */ s.jsx("span", { children: /* @__PURE__ */ s.jsx(F, { k: "recommendation.toolsTitle" }) }),
              W && /* @__PURE__ */ s.jsx("em", { children: /* @__PURE__ */ s.jsx(F, { k: "recommendation.loading" }) })
            ] }),
            D.length > 0 ? /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__toolShowcase", children: D.map((w) => {
              var Ct;
              const ce = Ae(At(w.product)), te = nn(w.product) ? Ae((Ct = w.product) == null ? void 0 : Ct.price) : void 0, Nt = R.includes(w.key);
              return /* @__PURE__ */ s.jsxs(
                "article",
                {
                  className: [
                    "tfdWizard__toolShowcaseItem",
                    w.imageUrl ? "tfdWizard__toolShowcaseItem--withImage" : "",
                    Nt ? "tfdWizard__toolShowcaseItem--selected" : ""
                  ].filter(Boolean).join(" "),
                  children: [
                    w.imageUrl && /* @__PURE__ */ s.jsx("img", { className: "tfdWizard__toolShowcaseImage", src: w.imageUrl, alt: w.name, loading: "lazy" }),
                    /* @__PURE__ */ s.jsxs("div", { children: [
                      /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__toolShowcaseTop", children: [
                        /* @__PURE__ */ s.jsx("b", { children: /* @__PURE__ */ s.jsx(se, { target: pt("tool", ul[w.key], "name"), value: w.name, title: "Tool: Name" }) }),
                        /* @__PURE__ */ s.jsx(
                          "button",
                          {
                            type: "button",
                            className: "tfdWizard__cartToggle",
                            onClick: () => ga(w.key),
                            "aria-pressed": Nt,
                            children: /* @__PURE__ */ s.jsx(F, { k: Nt ? "recommendation.selected" : "recommendation.select" })
                          }
                        )
                      ] }),
                      w.fromCombination && /* @__PURE__ */ s.jsx("small", { children: /* @__PURE__ */ s.jsx(F, { k: "recommendation.exactMatch" }) }),
                      w.description && /* @__PURE__ */ s.jsx("p", { children: /* @__PURE__ */ s.jsx(se, { target: pt("tool", ul[w.key], "description"), value: w.description, title: "Tool: Beschreibung", multiline: !0, nullable: !0 }) }),
                      /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__toolShowcaseMeta", children: [
                        w.level && /* @__PURE__ */ s.jsx("span", { children: w.level }),
                        te && /* @__PURE__ */ s.jsx("s", { className: "tfdWizard__priceOriginal", children: te }),
                        ce && /* @__PURE__ */ s.jsx("strong", { children: ce }),
                        w.product && /* @__PURE__ */ s.jsx("button", { type: "button", onClick: () => ml(w), children: /* @__PURE__ */ s.jsx(F, { k: "product.view" }) })
                      ] })
                    ] })
                  ]
                },
                w.key
              );
            }) }) : /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__miniNotice", children: /* @__PURE__ */ s.jsx(F, { k: "recommendation.noTools" }) })
          ] }),
          oe.recommendedProducts && oe.recommendedProducts.length > 0 && /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__recommendationPanel", children: [
            /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__recommendationPanelHeader", children: /* @__PURE__ */ s.jsx("span", { children: /* @__PURE__ */ s.jsx(F, { k: "recommendation.productsTitle" }) }) }),
            /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__toolShowcase", children: oe.recommendedProducts.map((w) => {
              const ce = Ae(At(w)), te = nn(w) ? Ae(w.price) : void 0;
              return /* @__PURE__ */ s.jsxs(
                "article",
                {
                  className: [
                    "tfdWizard__toolShowcaseItem",
                    w.imageUrl ? "tfdWizard__toolShowcaseItem--withImage" : ""
                  ].filter(Boolean).join(" "),
                  children: [
                    w.imageUrl && /* @__PURE__ */ s.jsx("img", { className: "tfdWizard__toolShowcaseImage", src: w.imageUrl, alt: w.title, loading: "lazy" }),
                    /* @__PURE__ */ s.jsxs("div", { children: [
                      /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__toolShowcaseTop", children: /* @__PURE__ */ s.jsx("b", { children: w.title }) }),
                      /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__toolShowcaseMeta", children: [
                        w.sku && /* @__PURE__ */ s.jsx("span", { children: w.sku }),
                        te && /* @__PURE__ */ s.jsx("s", { className: "tfdWizard__priceOriginal", children: te }),
                        ce && /* @__PURE__ */ s.jsx("strong", { children: ce })
                      ] })
                    ] })
                  ]
                },
                w.uid ?? w.title
              );
            }) })
          ] }),
          (D.length > 0 || (((v = oe.recommendedProducts) == null ? void 0 : v.length) ?? 0) > 0) && /* @__PURE__ */ s.jsx("p", { className: "tfdWizard__priceDisclaimer", children: /* @__PURE__ */ s.jsx(F, { k: "price.disclaimer" }) }),
          /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__extrasPanel", children: [
            /* @__PURE__ */ s.jsx("span", { children: /* @__PURE__ */ s.jsx(F, { k: "recommendation.extrasTitle" }) }),
            /* @__PURE__ */ s.jsx("ul", { children: L.map((w) => /* @__PURE__ */ s.jsx("li", { children: w === x ? /* @__PURE__ */ s.jsx(F, { k: "recommendation.extraApi" }) : w }, w)) })
          ] })
        ] });
      }
      case "contact":
        return Nf(we);
      default:
        return null;
    }
  };
  return /* @__PURE__ */ s.jsx(jh, { value: nf, children: /* @__PURE__ */ s.jsxs(
    "div",
    {
      className: `tfdWizard tfdWizard--${r}${o ? " tfdWizard--editing" : ""}`,
      style: { "--partner-wizard-primary": n },
      "data-testid": "partner-wizard",
      children: [
        o && /* @__PURE__ */ s.jsxs("div", { className: "tfdEdit__toolbar", children: [
          /* @__PURE__ */ s.jsxs("div", { className: "tfdEdit__toolbarInfo", children: [
            /* @__PURE__ */ s.jsx("b", { children: "Bearbeitungsmodus" }),
            /* @__PURE__ */ s.jsx("span", { children: "Markierte Texte anklicken · Fragen/Antworten hinzufügen und löschen (sofort gespeichert) · alle Schritte frei wählbar · es wird nichts gesendet" })
          ] }),
          /* @__PURE__ */ s.jsxs("div", { className: "tfdEdit__toolbarActions", children: [
            /* @__PURE__ */ s.jsx("button", { type: "button", onClick: () => ra((f) => !f), children: "Alle Texte" }),
            /* @__PURE__ */ s.jsx("button", { type: "button", onClick: () => {
              ll({}), Et("");
            }, disabled: kn === 0 || ol, children: "Verwerfen" }),
            /* @__PURE__ */ s.jsx("button", { type: "button", className: "tfdEdit__primary", onClick: cf, disabled: kn === 0 || ol, children: ol ? "Speichert…" : `Speichern (${kn})` })
          ] }),
          ia && /* @__PURE__ */ s.jsx("div", { className: "tfdEdit__notice", children: ia })
        ] }),
        /* @__PURE__ */ s.jsxs("header", { className: "tfdWizard__header", children: [
          /* @__PURE__ */ s.jsx("span", { children: /* @__PURE__ */ s.jsx(F, { k: "header.eyebrow" }) }),
          /* @__PURE__ */ s.jsx("h2", { children: /* @__PURE__ */ s.jsx(se, { target: { target: "client", field: "headline" }, value: a.headline, title: "Client: Headline" }) }),
          /* @__PURE__ */ s.jsx("p", { children: /* @__PURE__ */ s.jsx(se, { target: { target: "client", field: "introText" }, value: a.intro, title: "Client: Intro-Text", multiline: !0 }) })
        ] }),
        d && /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__error", children: d }),
        /* @__PURE__ */ s.jsx("div", { className: "tfdWizard__progress", children: /* @__PURE__ */ s.jsx("div", { style: { width: `${sf}%` } }) }),
        /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__layout", children: [
          /* @__PURE__ */ s.jsx("aside", { className: "tfdWizard__steps", "aria-label": "Wizard Schritte", children: ft.map((f, v) => {
            const x = [
              v === g ? "active" : "",
              v < g ? "done" : ""
            ].filter(Boolean).join(" ");
            return /* @__PURE__ */ s.jsxs(
              "button",
              {
                type: "button",
                className: x,
                onClick: () => {
                  (o || v <= g) && (h(v), c(""));
                },
                "data-testid": `step-${f.key}`,
                children: [
                  /* @__PURE__ */ s.jsx("b", { children: v < g ? /* @__PURE__ */ s.jsx("svg", { viewBox: "0 0 24 24", "aria-hidden": "true", focusable: "false", children: /* @__PURE__ */ s.jsx("path", { d: "M5 12.5l4.2 4.2L19 7" }) }) : v + 1 }),
                  /* @__PURE__ */ s.jsx("span", { children: /* @__PURE__ */ s.jsx(se, { target: f.id ? { target: "step", id: f.id, field: "label" } : null, value: f.label, title: "Schritt: Bezeichnung" }) })
                ]
              },
              f.key
            );
          }) }),
          /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__body", children: [
            /* @__PURE__ */ s.jsx("main", { className: "tfdWizard__mainPane", children: Cf() }),
            Ef()
          ] })
        ] }),
        /* @__PURE__ */ s.jsxs("div", { className: "tfdWizard__actions", children: [
          /* @__PURE__ */ s.jsx(
            "button",
            {
              type: "button",
              onClick: xf,
              disabled: g === 0,
              "data-testid": "wizard-back-button",
              children: /* @__PURE__ */ s.jsx(F, { k: "nav.back" })
            }
          ),
          we.type === "contact" ? /* @__PURE__ */ s.jsx(
            "button",
            {
              type: "button",
              onClick: jf,
              disabled: !fl || z,
              "data-testid": "wizard-submit-button",
              children: z ? /* @__PURE__ */ s.jsx(F, { k: "nav.sending" }) : /* @__PURE__ */ s.jsx(se, { target: pt("package", nr, "ctaLabel"), value: oe.cta, title: "Paket: Button-Text" })
            }
          ) : /* @__PURE__ */ s.jsx(
            "button",
            {
              type: "button",
              onClick: kf,
              disabled: !fl,
              "data-testid": "wizard-next-button",
              children: /* @__PURE__ */ s.jsx(F, { k: "nav.next" })
            }
          )
        ] }),
        Sf(),
        o && tf && /* @__PURE__ */ s.jsx(Th, { clientFields: mf, onClose: () => ra(!1) }),
        o && (Ve == null ? void 0 : Ve.kind) === "question" && /* @__PURE__ */ s.jsx(
          Ah,
          {
            stepType: Ve.step.type,
            stepLabel: Ve.step.label,
            busy: yn,
            onSubmit: (f) => ff(Ve.step, f),
            onClose: () => vn(null)
          }
        ),
        o && (Ve == null ? void 0 : Ve.kind) === "option" && /* @__PURE__ */ s.jsx(
          Lh,
          {
            questionTitle: Ve.question.title,
            withText: Ve.question.fieldType == null,
            busy: yn,
            onSubmit: (f) => pf(Ve.question, f),
            onClose: () => vn(null)
          }
        ),
        o && qt && /* @__PURE__ */ s.jsx(
          Eh,
          {
            request: qt,
            onApply: uf,
            onClose: () => il(null)
          },
          Xn(qt.target)
        )
      ]
    }
  ) });
}
class Xh extends HTMLElement {
  constructor() {
    super(...arguments);
    Ri(this, "root");
    Ri(this, "mount");
  }
  connectedCallback() {
    if (this.root)
      return;
    this.mount = document.createElement("div"), this.appendChild(this.mount);
    const n = this.getAttribute("client-id") || "no1", r = this.getAttribute("api-base-url") || "", l = this.getAttribute("primary-color") || "#E31E24", o = (this.getAttribute("theme") || "light") === "dark" ? "dark" : "light", a = this.getAttribute("edit-mode") === "1", u = this.getAttribute("edit-csrf") || "";
    this.style.setProperty("--partner-wizard-primary", l), this.root = Qd(this.mount), this.root.render(
      /* @__PURE__ */ s.jsx(
        Gh,
        {
          clientId: n,
          apiBaseUrl: r,
          primaryColor: l,
          theme: o,
          editMode: a,
          editCsrf: u
        }
      )
    );
  }
  disconnectedCallback() {
    var n;
    (n = this.root) == null || n.unmount(), this.root = void 0, this.mount = void 0;
  }
}
customElements.get("tfd-partner-wizard") || customElements.define("tfd-partner-wizard", Xh);
export {
  Gh as PartnerWizard
};
