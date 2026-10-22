
$(function () {
    $('body').on('mouseover', "img.user-img", function () {
        $('.span_username').html($(this).data('id'));
        $('.user_join_kit').html($(this).data('kit'));
        $('.user_paid_amount').html($(this).data('paid_amt'));
        // $('.sponsor-details').html($(this).data('name')+' ('+$(this).data('sponsor')+')');
        $('.sponsor-details').html($(this).data('name'));
        $('.user_join_date').html($(this).data('joindate'));
        $('.left_count').html($(this).data('left'));
        $('.right_count').html($(this).data('right'));
    });
});

function getTree(userId = null, round = 1) {
    let tree_number = $('input[name=tree_number]').val();
    let searchInputParent = $(document).find('.userId').parent();
    searchInputParent.find('.text-danger').remove();
    $.ajax({
        type: 'GET',
        url: baseUrl() + '/member/network/tree/' + tree_number,
        data: {
            username: userId,
            round: round,
        },
        success: function (result) {
            $('#tree').html(result);
            $("img.user-img").easyTooltip({ useElement: "user_1" });
        },
        error: function (err) {
            if (err.responseJSON.status == false) {
                console.log(searchInputParent.append('<span class="text-danger">' + err.responseJSON.message + '</span>'));
            }

        }
    });
}


function handleEmptyNode(position, parent, sponsor, path) {
    console.log(parent);
    if (parent.trim() !== '') {
        window.location.href = path + '?position=' + position + '&parent=' + parent + '&sponsor=' + sponsor;
    }
}
