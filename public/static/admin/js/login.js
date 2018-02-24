$('#kanbuq').click(function () {
    $(this).prev().attr('src', $(this).prev().attr('src') + '?' + Math.random());
});
var _userName = $("#username");
var _passwd = $("#password");
var _code = $("#code");
var _online = $("#online");
var testName = /^[0-9a-zA-Z]{4,15}$/;
var testPasswd = /^[0-9a-zA-Z]{6,15}$/;
var testCode = /^[0-9a-zA-Z]{4}$/;
// _userName.blur(function () {
// checkUserName();
// });
function checkUserName() {
    var tmpVal = _userName.val().trim();
    if (tmpVal == '') {
        layer.msg('请填写用户名称', {icon: 5});
        return false;
    }
    else if (!testName.test(tmpVal)) {
        layer.msg('用户名称格式有误', {icon: 5});
        return false;
    }
    return true;
}

// _passwd.blur(function () {
// checkPassWd();
// });
function checkPassWd() {
    var tmpVal = _passwd.val().trim();
    if (tmpVal === '') {
        layer.msg('请填写密码', {icon: 5});
        return false;
    }
    else if (!testPasswd.test(tmpVal)) {
        layer.msg('用户密码格式有误', {icon: 5});
        return false;
    }
    return true;
}

// _code.blur(function () {
// checkCode();
// });
function checkCode() {
    var tmpVal = _code.val().trim();
    if (tmpVal === '') {
        layer.msg('请填写验证码', {icon: 5});
        return false;
    }
    // else if(!testCode.test(tmpVal))
    // {
    //     layer.msg('验证码格式有误', {icon: 5});
    //     return false;
    // }
    return true;
}

function login() {
    if (!checkUserName()) {
        return
    }
    if (!checkPassWd()) {
        return
    }
    if (!checkCode()) {
        return
    }
    var online = 0;
    if (_online.is(':checked')) {
        online = 1;
    }
    $.post('', {
        username: _userName.val().trim(),
        password: _passwd.val().trim(),
        code: _code.val().trim(),
        online: online
    }, function (data) {
        if (data.status === 'y') {
            window.location.href = data.url;
        }
        else {
            layer.msg(data.info, {icon: 5});
        }
    }, 'json');
}
