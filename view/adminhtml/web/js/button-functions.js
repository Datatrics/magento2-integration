require([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'loader'
], function ($, modal) {

    /**
     * @param{String} type - type of modal css selector.
     * @param{Object} options - modal options.
     */
    function initModal(type, options) {
        const modalId = `#mm-datatrics-result_${type}-modal`;
        if (!$(modalId).length) return;
        
        let defaultOptions = {
            modalClass: 'mm-datatrics-modal',
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: options.title || '',
            buttons: [
                {
                    text: $.mage.__('ok'),
                    class: 'action primary-new',
                    click: function () {
                        this.closeModal();
                    },
                }
            ]
        };

        // Additional buttons for downloading
        if (options.buttons) {
            let additionalButtons = {
                text: $.mage.__('download as .txt file'),
                class: 'mm-datatrics-button__download mm-datatrics-icon__download-alt',
                click: () => {
                    let elText = document.getElementById(`mm-datatrics-result_${options.buttons}`).innerText || '',
                        link = document.createElement('a');

                    link.setAttribute('download', `${options.buttons}-log.txt`);
                    link.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(elText));
                    link.click();
                },
            };

            defaultOptions.buttons.unshift(additionalButtons);
        }

        modal(defaultOptions, $(modalId));
        $(modalId).loader({texts: ''});
    }

    var successHandlers = {
        /**
         * @param{Object[]} obj - Ajax request response data.
         * @param{Object} $container - jQuery container element.
         * @param{String} type - debug || error || test.
         */
        logs(response, $container, type) {
            if (Array.isArray(response.result)) {
                if (type === 'debug' || type === 'error') {
                    response = `<ul>${response.result.map((data) => this.tmpLogs(type, data)).join('')}</ul>`;                              
                }

                if (type === 'test') {
                    response = `${response.result.map((data) => this.tmpTest(type, data)).join('')}`; 
                }
            }

            $container.find('.result').empty().append(response);
        },

        tmpLogs(type, data) {
            return `<li class="mm-datatrics-result_${type}-item">
                        <strong>${data.date}</strong>
                        <p>${data.msg}</p>
                    </li>`;
        },

        tmpTest(type, data) {
            let supportLinkHtml = '';
                resultMsg = data.result_code === 'failed' ? data.result_msg : '';
                resultText = data.result_code === 'success' 
                                ? $.mage.__('Passed') 
                                : $.mage.__('Failed');

            if (data.support_link) {
                supportLinkHtml = `<a target="_blank" href="${data.support_link}"
                                      class="mm-datatrics-icon__help-rounded">
                                        ${$.mage.__('More information')}
                                    </a>`;
            }

            return `<li class="mm-datatrics-result_${type}-item ${data.result_code}">
                        <strong>${resultText}</strong>
                        <div>
                            <p>${data.test}</p>
                            <p><em>${resultMsg}</em></p>
                        </div>
                        ${supportLinkHtml}
                    </li>`;
        },

        /**
         * @param{Object[]} result - Ajax request response data.
         * @param{Object} $container - jQuery container element.
         */
        version(response, $container) {
            let resultHTML = '';
                resultText = $.mage.__('Great, you are using the latest version.');
                resultClass = 'up';
                currentVersion = response.result.current_verion.replace(/v|version/gi, '');
                latestVersion = response.result.last_version.replace(/v|version/gi, '');

            if (currentVersion !== latestVersion) {
                resultClass = 'down';
                resultText = $.mage.__('There is a new version available <span>(%1)</span> see <button type="button" id="mm-datatrics-button_changelog">changelog</button>.')
                    .replace('%1', latestVersion);
            }

            resultHTML = `<strong class="mm-datatrics-version mm-datatrics-icon__thumbs-${resultClass}">
                            ${resultText}
                         </strong>`;

            $container.html(resultHTML);
        },

        /**
         * @param{Object[]} result - Ajax request response data.
         * @param{Object} $container - jQuery container element.
         */
        changelog(response, $container) {
            var listHTML = Object.keys(response).map((version) => {
                return `<li class="mm-datatrics-result_changelog-item">
                            <b>${version}</b>
                            <span class="mm-datatrics-divider">|</span>
                            <b>${response[version].date}</b>
                            <div>${response[version].changelog}</div>
                        </li>`;
            });

            $container.find('.result').empty().append(listHTML.join(''));
        },
    }

    // init modals
    $(() => {
        initModal('debug',      { title: $.mage.__('last 100 debug log records'), buttons: 'debug' });
        initModal('error',      { title: $.mage.__('last 100 error log records'), buttons: 'error' });
        initModal('test',       { title: $.mage.__('Self-test') });
        initModal('changelog',  { title: $.mage.__('Changelog') });
    });

    // init loader on the Check Version block
    $('.mm-datatrics-result_version-wrapper').loader({texts: ''});

    /**
     * Ajax request event
     */
    $(document).on('click', '[id^=mm-datatrics-button]', function () {
        let action = this.id.split('_')[1],
            $modal = $(`#mm-datatrics-result_${action}-modal`),
            $result = $(`#mm-datatrics-result_${action}`);

        if (action === 'version') {
            $(this).fadeOut(300).addClass('mm-datatrics-disabled');
            $modal = $(`.mm-datatrics-result_${action}-wrapper`);
            $modal.loader('show');
        } else {
            $modal.modal('openModal').loader('show');
        }

        $result.hide();

        fetch($modal.attr('data-mm-datatrics-endpoind-url'))
            .then((res) => res.clone().json().catch(() => res.text()))
            .then((data) => {
                const func = action === 'debug' || 
                             action === 'error' || 
                             action === 'test' ? 'logs' : action;

                successHandlers[func](data, $result, action);
                $result.fadeIn();
                $modal.loader('hide');
            });
    });
});
