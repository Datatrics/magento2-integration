require([
    'jquery',
    'mage/translate',
    '!domReady'
], function ($, $t) {

    let comment = $('.mm-datatrics-heading-comment'),
        showMoreLessBtnHtml = `
            <div class="mm-datatrics-show-more-actions hidden">
                <a href="javascript:void(0)" class="mm-datatrics-show-btn-more">
                    ${$t('Show more.')}
                </a>
            </div>`;

    if(comment.length) {
        comment.parent().append(showMoreLessBtnHtml);

        $(document).on('click', '.mm-datatrics-show-more-actions a', (e) => {
            let button = $(e.target),
                parent = $(e.target).closest('.value').find('.mm-datatrics-heading-comment');

            if (parent.hasClass('show')) {
                parent.removeClass('show');
                button.text($t('Show more.'));
            } else {
                parent.addClass('show');
                button.text($t('Show less.'));
            }
        });

        window.addEventListener("load", isShowMore);
        window.addEventListener("resize", isShowMore);
    }

    function isShowMore() {
        Array.from(comment).forEach((item) => {
            const BTN = item.closest('td').querySelector('.mm-datatrics-show-more-actions');
            item.scrollHeight <= 55 ? BTN.classList.add('hidden') : BTN.classList.remove('hidden');
        });
    }
});
