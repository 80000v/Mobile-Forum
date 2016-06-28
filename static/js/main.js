$(function () {

    // //fast click
    FastClick.attach(document.body);

    //选项卡
    (function () {
        var tabs_title = $(".tabs-title >li");
        var tabs_content = $(".tabs-content >li");
        //记录前一个
        var pre_tabs_title = tabs_title.eq(0);
        var pre_tabs_content = tabs_content.eq(0);
        tabs_title.each(function (i) {
            $(this).on('click', function () {
                pre_tabs_title.removeClass('active');
                pre_tabs_title = $(this).addClass('active');
                pre_tabs_content.hide();
                pre_tabs_content = tabs_content.eq(i).show();
            });
        });
    })();


    //表单验证构造函数
    function Form(obj) {
        //表单对象
        this.form = obj;
        //输入框的信息
        this.input = obj.find("input[type=text],input[type=password],textarea");
    }

    //原型增加方法
    Form.prototype = {


        //验证一个input是否有效，并添加合适的样式
        is_right: function (ipt) {

            if (new RegExp(ipt.data('pattam')).test(ipt.val())) {
                ipt.removeClass('notice');
                return true;
            } else {
                ipt.addClass('notice');
                return false;
            }
        },
        //为input绑定事件blur事件
        bind_event: function () {
            var that = this;
            that.input.each(function () {
                $(this).on('blur', function () {
                    that.is_right($(this));
                });
            });
        },
        //验证一个表单（表单内所有的input）是否有效
        verify_all: function () {
            var flag = true;
            var that = this;
            this.input.each(function (i) {
                //每一个input对象
                if (!that.is_right($(this))) {
                    flag = false;
                }
            });
            return flag;
        },

        //表单处理类开始工作
        start: function () {
            var that = this;
            //为表单绑定blur事件
            that.bind_event();
            //绑定submit事件
            that.form.on('submit', function (event) {
                event.preventDefault();
                //验证表单内所有的input
                if (that.verify_all()) {
                    //ajax提交数据
                    $.post(that.form.attr('action'), that.form.serialize(), function (data) {
                        //登录成功
                        if (data.status == 1) {
                            that.form.find('input[type=submit]').prop('disabled', true);
                        }
                        ajax_result(data.info, data.url);
                    });
                } else {
                    showNotice('请正确填写完表单再提交');
                }
            });

        }
    };

    //表单验证
    (new Form($("#register"))).start();
    (new Form($("#login"))).start();
    (new Form($("#user-findpwd"))).start();
    (new Form($("#user-modify"))).start();
    (new Form($("#user-password"))).start();

    //用户中心头像上传处理
    $("#portrait").each(function () {
        var that = $(this);
        new AjaxUpload(that, {
            action: that.data("url"),
            type: 'post',
            autoSubmit: true,
            name: 'portrait',
            responseType: 'json',
            onComplete: function (file, resp) {
                //成功了
                if (resp.status == 1) {
                    $(this._button).attr('src', resp.src);
                }
                showNotice(resp.info);
            }
        });
    });


    //论坛帖子图片和附件上传
    $(".edit-upload").each(function () {
        var that = $(this);
        new AjaxUpload(that, {
            action: that.data("url"),
            type: 'post',
            autoSubmit: true,
            name: 'edit',
            responseType: 'json',
            onSubmit: function () {
                this.setData({
                    'price': $("#price").val()
                });
            },
            onComplete: function (file, resp) {
                if (resp.status == 1) {
                    var text_wrap = $("#upload-textarea");//扑捉到的textarea文本框
                    text_wrap.val(text_wrap.val() + resp.text);
                }
                ajax_result(resp.info, resp.url);
            }
        });
    });

    //帖子页面的回帖按钮
    (function () {
        var appendLocal = $(".reply-list");
        var textarea = $('.edit-area');
        var edit = $(".edit");
        var uid_input = $('#uid-input');

        //回复按钮 ,因为新插入的元素也要绑定事件，所以要用事件监听的方式
        appendLocal.on('click', '.reply-user', function (e) {
            uid_input.val($(e.target).data('uid'));
            textarea.focus().val('回复 ' + $(e.target).data('nickname') + ':');
        });

        //提交按钮
        $(".edit-smt").click(function () {
            edit.submit();
            //提交完毕之后，清空uid,
            uid_input.val('');
        });

        edit.submit(function (e) {
            e.preventDefault();
            var that = $(this);
            $.post(that.attr('action'), that.serialize(), function (data) {
                if (data.status == 1) {
                    var temp = $('<li class="reply-item clearfix"><div class="reply-item-left"><a href="user-' + data.user_id + '.html"><img src="' + data.user_icon + '" alt="' + data.user_nickname + '"></a><p>#' + data.cmt_num + '</p></div><div class="reply-item-right"><div><a href="user-' + data.user_id + '.html">' + data.user_nickname + '</a><a class="right reply-user" data-uid="' + data.user_id + '" data-nickname="' + data.user_nickname + '" href="javascript:void(0);">回复</a><span class="reply-item-time"><i class="icon icon-time"></i>' + data.cmt_time + '</span></div><div>' + data.cmt + '</div></div></li>');
                    temp.appendTo(appendLocal).hide().show(500);
                    textarea.val('');
                }
                ajax_result(data.info, data.url);
            });
        });

    })();

    //收藏 ok
    click_ajax($(".collection"), function (data, ele) {

        if (data.status == 1) {
            ele.find("i:first-child").toggleClass('icon-favor icon-favorfill');
        }
        ajax_result(data.info, data.url);
    });

    //点赞处理 ok
    click_ajax($(".praise"), function (data, ele) {
        if (data.status == 1) {
            ele.find("i:first-child").toggleClass('icon-like icon-likefill');
            ele.find("span:last-child").text(data.num);
        }
        ajax_result(data.info, data.url);
    });

    //加好友处理 ok
    click_ajax($(".toggle-friend"), function (data, ele) {
        if (data.status == 1) {
            ele.text(data.text);
        }
        ajax_result(data.info, data.url);
    });

    //锁帖处理 ok
    click_ajax($('.theme-lock'), function (data, ele) {
        if (data.status == 1) {
            ele.text(data.text);
        }
        ajax_result(data.info, data.url);
    });

    //删帖处理 ok
    click_ajax($('.theme-del'), function (data, ele) {
        ajax_result(data.info, data.url);
    }, "确定要删除吗");

    //置顶操作
    click_ajax($('.theme-top'), function (data, ele) {
        if (data.status == 1) {
            ele.text(data.text);
        }
        ajax_result(data.info, data.url);
    });

    //admin

    //删除版主
    click_ajax($('.moderator-del'), function (data, ele) {
        if (data.status == 1) {
            ele.parent().parent().hide('liner', function () {
                $(this).remove();
            });
        }
        showNotice(data.info);
    });

    //删除版块
    click_ajax($('.section-del'), function (data, ele) {
        if (data.status == 1) {
            ele.parent().parent().hide('liner', function () {
                $(this).remove();
            });
        }
        showNotice(data.info);
    });

    //下一页处理 ok
    (function () {
        //跳转的输入框
        var page_input = $(".page-input");
        //跳转的a连接（用来点击）
        var page_redirect = $(".page-redirect");
        //下一页的按钮
        var page_next = $(".page-next");
        page_input.focus(function () {
            page_redirect.show();
            page_next.hide();
        }).blur(function () {
            var value = page_input.val();
            if ($(this).attr('value') != value) {
                window.location.href = $(this).data('url').replace('#', value);
            } else {
                page_redirect.hide();
                page_next.show();
            }
        }).keydown(function (e) {
            var value = page_input.val();
            if (e.keyCode == 13 && $(this).attr('value') != value) {
                window.location.href = $(this).data('url').replace('#', value);
            }
        });
    })();

    //搜索框
    (function () {
        $(".search-btn").click(function () {
            $(".search").submit();
        });
    })();

    //验证码点击切换
    (function () {
        var verify_code = $("#verify_code");
        var verify_src = verify_code.attr('src');
        verify_code.click(function () {
            $(this).attr('src', verify_src + '#' + Math.random());
        });
    })();


    /*---------------------------快捷函数----------------------------*/
    //显示提示信息
    function showNotice(message) {
        $("#notice-message").text(message).finish().show(200).delay(1500).hide(200);
    }


    //显示信息并定时跳转
    function ajax_result(message, url) {
        if (url) {
            showNotice(message + "，正在跳转");
            setTimeout(function () {
                window.location.href = url;
            }, 1000);
        } else {
            showNotice(message);
        }
    }

    //用户点击进行ajax请求的a链接,如果有第三个参数，还会弹出一个框去确认是否进行这个操作
    function click_ajax(ele, handler, cfm) {
        ele.click(function (e) {
            e.preventDefault();
            if (cfm === undefined) {
                $.post($(this).attr('href'), function (data) {
                    handler(data, ele, e);
                });
            } else {
                if (confirm(cfm)) {
                    $.post($(this).attr('href'), function (data) {
                        handler(data, ele, e);
                    });
                }
            }
        });
    }
});