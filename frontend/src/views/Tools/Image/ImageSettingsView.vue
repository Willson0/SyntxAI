<script>
export default {
    name: "ImageSettingsView",
    data () {
        return {
            defaultData: {
                prompt: '',
                no: '',
                version: '7',
                style: '',
                quality: '1',
                ar: '4',
                chaos: '0',
                // seed: random(-1, 4294967295).toString(),
                stylize: '100',
                weird: '0',
                exp: '1',
                //creative: false,
                tile: false,
                sref: '',
                sw: '0',
                cref: '',
                cw: '0',
                oref: '',
                ow: '0',
            },
            result: null,
            aspectRatioOptions: [
                '1:2',
                '9:16',
                '2:3',
                '3:4',
                '4:5',
                '1:1',
                '5:4',
                '4:3',
                '3:2',
                '16:9',
                '2:1',
            ]
        }
    },
    created () {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = '/src/assets/Image.css';
        link.id = 'component-styles'; // Чтобы потом удалить
        document.head.appendChild(link);
    },
    beforeUnmount() {
        // Удаляем стили при уничтожении компонента
        const link = document.getElementById('component-styles');
        if (link) link.remove();
    },
    mounted () {
        document.body.style.fontFamily = "'Montserrat', sans-serif";
        document.body.style.padding = "0"

        this.result = {
            ...this.getResult()
        };

        this.setAspectRatioOptions();

        this.setAspectRatioImage(this.getResult().ar);
        this.renderStyleOptions(this.getResult().version);
        this.renderSrefBlock(this.getResult().sref);
        this.renderCrefBlock(this.getResult().cref);
        this.renderOrefBlock(this.getResult().oref);
        
        this.showHintPrompt();

        for (var key in this.defaultData) {
            if (this.defaultData.hasOwnProperty(key)) {
                this.setDefaultValues(key);
                this.setEvents(key);
            }
        }
        
        this.initHideStickyHeader();
        
        
        Telegram.WebApp.MainButton.setParams({
            text: 'Скопировать и закрыть'
        });
        Telegram.WebApp.MainButton.onClick(() => {
            const prompt = resultToText();
            this.copyToClipboard(prompt);
            Telegram.WebApp.showAlert(`Результат скопирован в буфер обмена\n\nЕсли результат не скопирован, скопируйте его вручную использую кнопку Показать промпт`, () => {
                Telegram.WebApp.close();
            })
        });
        Telegram.WebApp.MainButton.show();
    },
    methods: {
        setAspectRatioOptions () {
            const select = document.getElementById('ar_input');
            this.aspectRatioOptions.forEach((value, index) => {
                const option = document.createElement('option');
                option.value = index;
                option.text = value;
                select.appendChild(option);
            });
        },
        setDefaultValues (key) {
            const data = this.getResult();
            if(document.getElementById(key).type === 'checkbox') {
                document.getElementById(key).checked = data[key];
            } else {
                document.getElementById(key).value = data[key];
            }
            if(document.getElementById(`${key}_input`)) {
                document.getElementById(`${key}_input`).value = data[key];
            }
        },
        setAspectRatioImage (value) {
            const aspectRatioImage = document.getElementById('ar_image');
            aspectRatioImage.className = `aspect-ratio aspect-ratio--${this.aspectRatioOptions[value].replace(':', '-')}`;
            aspectRatioImage.innerText = this.aspectRatioOptions[value];
        },
        renderStyleOptions (value) {
            const selectElement = document.getElementById('style');
            const options = selectElement.options;
            if(value.match(/niji/)) {
                for(let i = 0; i < options.length; i++) {
                    options[i].disabled = false;
                }
            } else {
                for(let i = 0; i < options.length; i++) {
                    if(options[i].value.includes('raw') || options[i].value === '') {
                        options[i].disabled = false;
                    } else {
                        options[i].disabled = true;
                    }
                }
            }
        },
        renderSrefBlock (value) {
            if(!value) {
                document.getElementById('sw').disabled = true;
                document.getElementById('sw_input').disabled = true;
            } else {
                document.getElementById('sw').disabled = false;
                document.getElementById('sw_input').disabled = false;
            }
        },
        renderCrefBlock (value) {
            if(!value) {
                document.getElementById('cw').disabled = true;
                document.getElementById('cw_input').disabled = true;
            } else {
                document.getElementById('cw').disabled = false;
                document.getElementById('cw_input').disabled = false;
            }
        },
        random (min, max) {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        },
        getResult () {
            const result = localStorage.getItem('result');
            if(result) {
                return JSON.parse(result);
            }

            return this.defaultData;
        },
        showPromptResult () {
            const promptResult = document.getElementById('prompt-result');
            const promptResultBtn = document.getElementById('prompt-result-btn');
            promptResult.style.display = 'block';
            promptResultBtn.style.display = 'none';
            promptResult.value = this.resultToText();
        },
        setPromptToTextarea () {
            const prompt = document.getElementById('prompt-result');
            prompt.value = this.resultToText();
        },
        copyToClipboard (text) {
            var textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();

            const fallbackCopy = () => {
                try {
                    var successful = document.execCommand('copy');
                    var msg = successful ? 'successful' : 'unsuccessful';
                    console.log('Fallback: Copying text command was ' + msg);
                } catch (err) {
                    console.error('Fallback: Oops, unable to copy', err);
                }
            }
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function() {
                    console.log('Copying to clipboard was successful!');
                }, function(err) {
                    console.error('Could not copy text: ', err);
                    fallbackCopy();
                });
            } else {
                fallbackCopy();
            }
        },
        initHideStickyHeader () {
            const stickyHeader = document.getElementById('sticky-header');
            const hideButton = document.getElementById('sticky-header-hide-button');
            hideButton.addEventListener('click', () => {
                stickyHeader.classList.toggle('sticky--hide');
            });
        },
        validateNumberInput (e) {
            const input = e.target;
            const value = parseInt(input.value);
            const min = parseInt(input.min);
            const max = parseInt(input.max);
            let result = value;

            if(value < min) {
                result = min;
            } else if(value > max) {
                result = max;
            }

            result = parseInt(result);
            input.value = result;

            return result;
        },
        setEvents (key) {
            let event = (e) => {};
            switch (key) {
                case 'ar':
                    event = (e) => {
                        this.result[key] = e.target.value;
                        this.setAspectRatioImage(e.target.value);
                        this.showHintPrompt();
                        this.saveResultToLocalStorage();
                        this.setPromptToTextarea();
                    };
                    break;
                case 'creative':
                case 'tile':
                    event = (e) => {
                        this.result[key] = e.target.checked;
                        this.showHintPrompt();
                        this.saveResultToLocalStorage();
                        this.setPromptToTextarea();
                    };
                    break;
                case 'sref':
                    event = (e) => {
                        this.result[key] = e.target.value;

                        this.renderSrefBlock(e.target.value);
                        this.showHintPrompt();
                        this.saveResultToLocalStorage();
                        this.setPromptToTextarea();
                    };
                    break;
                case 'cref':
                    event = (e) => {
                        this.result[key] = e.target.value;

                        this.renderCrefBlock(e.target.value);
                        this.showHintPrompt();
                        this.saveResultToLocalStorage();
                        this.setPromptToTextarea();
                    };
                    break;
                case 'oref':
                    event = (e) => {
                        this.result[key] = e.target.value;

                        this.renderOrefBlock(e.target.value);
                        this.showHintPrompt();
                        this.saveResultToLocalStorage();
                        this.setPromptToTextarea();
                    };
                    break;
                case 'version':
                    const version = document.getElementById('version');

                    // Sref & Cref block
                    const srefAndCrefBlock = function(target) {
                        if(target.value === '6' || target.value === '6.1') {
                            document.getElementById('sref-block').style.display = 'block';
                            document.getElementById('cref-block').style.display = 'block';
                        } else {
                            document.getElementById('sref-block').style.display = 'none';
                            document.getElementById('cref-block').style.display = 'none';
                        }
                    };

                    srefAndCrefBlock(version);

                    // oref block
                    const orefBlock = function(target) {
                        if(target.value === '7') {
                            document.getElementById('sref-block').style.display = 'block';
                            document.getElementById('oref-block').style.display = 'block';
                            document.getElementById('cref-block').style.display = 'none';
                        } else {
                            document.getElementById('oref-block').style.display = 'none';
                        }
                    };

                    orefBlock(version);

                    // exp block
                    const expBlock = function(target) {
                        if(target.value === '7') {
                            document.getElementById('exp-block').style.display = 'block';
                        } else {
                            document.getElementById('exp-block').style.display = 'none';
                        }
                    };

                    expBlock(version);

                    event = (e) => {
                        this.result[key] = e.target.value;

                        srefAndCrefBlock(e.target);
                        orefBlock(e.target);
                        expBlock(version);

                        // Quality
                        const qualityElement = document.getElementById('quality');
                        qualityElement.value = '1';

                        const $value025 = qualityElement.querySelector('option[value="0.25"]')
                        if($value025) {
                            qualityElement.removeChild($value025);
                        }

                        const $value2 = qualityElement.querySelector('option[value="2"]')
                        if($value2) {
                            qualityElement.removeChild($value2);
                        }

                        if(e.target.value === '6.1' || e.target.value === '7') {
                            const option = document.createElement('option');
                            option.value = '2';
                            option.text = '2';
                            qualityElement.prepend(option);

                        } else {
                            const option = document.createElement('option');
                            option.value = '0.25';
                            option.text = '0.25';
                            qualityElement.appendChild(option);
                        }

                        const selectElement = document.getElementById('style');

                        selectElement.value = '';
                        // create a new event
                        const event = new Event('input', {
                            bubbles: true,
                            cancelable: true,
                        });

                        // dispatch the event
                        selectElement.dispatchEvent(event);

                        this.renderStyleOptions(e.target.value);
                        this.showHintPrompt();
                        this.saveResultToLocalStorage();
                        this.setPromptToTextarea();
                    };
                    break;
                default:
                    event = (e) => {
                        const value = e.target.type === 'number' ? this.validateNumberInput(e) : e.target.value;
                        this.result[key] = value;
                        this.showHintPrompt();
                        this.saveResultToLocalStorage();
                        this.setPromptToTextarea();
                    };
                    break;
            }

            document.getElementById(key).addEventListener('input', event);
            if(document.getElementById(`${key}_input`)) {
                document.getElementById(`${key}_input`).addEventListener('change', event);
            }
        },
        saveResultToLocalStorage () {
            localStorage.setItem('result', JSON.stringify(this.result));
        },
        resetResult () {
            localStorage.removeItem('result');
            location.reload();
        },
        updateInput (value, id) {
            document.getElementById(id).value = value;
        },
        resultToArray (isRemoveMainPrompt = false) {
            const resultArr = [];
            for (var key in this.result) {
                if (this.result.hasOwnProperty(key) && this.result[key]) {
                    switch (key) {
                        case 'prompt':
                            if(!isRemoveMainPrompt) {
                                resultArr.push(this.result[key])
                            }
                            break;
                        case 'version':
                            if(this.result[key].match(/niji/)) {
                                resultArr.push(`--${this.result[key]}`);
                            } else {
                                resultArr.push(`--v ${this.result[key]}`);
                            }
                            break;
                        case 'ar':
                            resultArr.push(`--ar ${this.aspectRatioOptions[this.result[key]]}`);
                            break;
                        case 'creative':
                        case 'tile':
                            if(this.result[key]) {
                                resultArr.push(`--${key}`);
                            }
                            break;
                        case 'sref':
                            if(this.result['version'] === '6' || this.result['version'] === '6.1' || this.result['version'] === '7') {
                                resultArr.push(`--${key} ${this.result[key]}`);
                            }
                            break;
                        case 'sw':
                            if(this.result['sref'] && (this.result['version'] === '6' || this.result['version'] === '6.1' || this.result['version'] === '7')) {
                                resultArr.push(`--${key} ${this.result[key]}`);
                            }
                            break;
                        case 'cref':
                            if(this.result['version'] === '6' || this.result['version'] === '6.1' || this.result['version'] === '7') {
                                resultArr.push(`--${key} ${this.result[key]}`);
                            }
                            break;
                        case 'cw':
                            if(this.result['cref'] && (this.result['version'] === '6' || this.result['version'] === '6.1' || this.result['version'] === '7')) {
                                resultArr.push(`--${key} ${this.result[key]}`);
                            }
                            break;
                        case 'oref':
                            if(this.result['version'] === '7') {
                                resultArr.push(`--${key} ${this.result[key]}`);
                            }
                            break;
                        case 'ow':
                            if(this.result['oref'] && this.result['version'] === '7') {
                                resultArr.push(`--${key} ${this.result[key]}`);
                            }
                            break;
                        case 'exp':
                            if(this.result['version'] === '7') {
                                resultArr.push(`--${key} ${this.result[key]}`);
                            }
                            break;
                        default:
                            resultArr.push(`--${key} ${this.result[key]}`);
                            break;
                    }
                }
            }

            return resultArr;
        },
        resultToText () {
            return this.resultToArray().join(' ');
        },
        showHintPrompt () {
            const hintPrompt = document.getElementById('hint-prompt');
            hintPrompt.innerHTML = '';
            const resultArrWithoutPrompt = this.resultToArray(true);

            resultArrWithoutPrompt.forEach((value) => {
                const option = document.createElement('div');
                option.className = 'prompt-params__option';
                option.innerText = value;
                hintPrompt.appendChild(option);
            });
        },
        renderOrefBlock (value) {
            if(!value) {
                document.getElementById('ow').disabled = true;
                document.getElementById('ow_input').disabled = true;
            } else {
                document.getElementById('ow').disabled = false;
                document.getElementById('ow_input').disabled = false;
            }
        }
    }
}
</script>

<template>
    <!-- Prompt -->
    <div id="sticky-header" class="sticky">
        <div class="form-group box padding border-radius" style="min-height: 73px;">
            <textarea id="prompt" name="prompt" class="full-width-input" placeholder="Введите промпт" rows="3" style="resize: vertical; min-height: 50px; padding: 7px !important"></textarea>

            <div id="hint-prompt" class="prompt-params"></div>

            <button id="sticky-header-hide-button" type="button" class="hide-button"></button>
        </div>
    </div>

    <section class="margin image-container">
        <div class="settings-form">
            <div class="form-group box padding border-radius">
                <label for="no" class="label-hint">
                    <div class="hint" data-tooltip="Исключение деталей из изображения">i</div>
                    Исключения (--no)        </label>
                <input type="text" id="no" style="padding: 10px !important" name="no" class="full-width-input" placeholder="Введите исключения" value="">
            </div>

            <div class="form-group box padding border-radius flex">
                <div class="label-and-checkbox">
                    <input type="checkbox" id="tile" name="tile">
                    <label for="tile" class="label-hint">
                        <div class="hint hint--right" data-tooltip="Бесшовное изображение (режим плитки) с повторяющимися элементами (--tile)">i</div>
                        Бесшовный режим (--tile)          </label>
                </div>
            </div>

            <div class="form-group box padding border-radius flex">
                <div class="label-and-select">
                    <label for="version" style="padding: 0; font-size:11px;">
                        Версия модели          </label>
                    <select id="version" name="version" class="select-input">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="5.1">5.1</option>
                        <option value="5.2">5.2</option>
                        <option value="6">6</option>
                        <option value="6.1">6.1</option>
                        <option value="7" selected="">7</option>
                        <option value="niji 5">niji 5</option>
                        <option value="niji 6">niji 6</option>
                    </select>
                </div>

                <!-- Styles -->
                <div class="label-and-select">
                    <label for="style" class="label-hint">
                        <div class="hint hint--right" data-tooltip="Параметр --style заменяет стандартную эстетику некоторых Midjourney моделей">i</div>
                        Стиль          </label>
                    <select id="style" name="style" class="select-input">
                        <option value="" selected="">-</option>
                        <option value="raw">raw</option>
                        <option value="cute" disabled>cute</option>
                        <option value="expressive" disabled>expressive</option>
                        <option value="original" disabled>original</option>
                        <option value="scenic" disabled>scenic</option>
                    </select>
                </div>

                <!-- Quality -->
                <div id="quality-block" class="label-and-select">
                    <label for="quality" class="label-hint">
                        <div class="hint hint--left" data-tooltip="Качество и уровень детализации изображения (--quality)">i</div>
                        Качество          </label>
                    <select id="quality" name="quality" class="select-input">
                        <option value="2" selected="">2</option>
                        <option value="1" selected="">1</option>
                        <option value="0.5">0.5</option>
                    </select>
                </div>
            </div>

            <!-- Aspect ratio -->
            <div class="form-group box padding border-radius">
                <div class="label-and-input">
                    <label for="ar">
                        Соотношение сторон (--ar)            </label>
                    <select id="ar_input" name="ar_input" class="select-input select_input" onchange="updateInput(this.value, 'ar')"></select>
                </div>
                <input style="background: #3a3a3a; border: 1px solid grey; box-sizing: border-box; -webkit-appearance: auto !important; appearance: auto !important" type="range" id="ar" name="ar" min="0" max="10" value="0" @input="updateInput($event.target.value, 'ar_input')" class="full-width-range">

                <div class="aspect-ratio-wrapper">
                    <div id="ar_image" class="aspect-ratio"></div>
                </div>
            </div>

            <!-- Chaos -->
            <div class="form-group box padding border-radius">
                <div class="label-and-input">
                    <label for="chaos" class="label-hint">
                        <div class="hint" data-tooltip="Случайность и вариативность">i</div>
                        Хаос (--chaos)            </label>
                    <input type="number" id="chaos_input" name="chaos_input" min="0" max="100" value="0" @input="updateInput($event.target.value, 'chaos')" class="number-input">
                </div>
                <input type="range" id="chaos" name="chaos" min="0" max="100" value="0" @input="updateInput($event.target.value, 'chaos_input')" class="full-width-range">
            </div>

            <div class="form-group box padding border-radius">
                <div class="label-and-input">
                    <label for="stylize" class="label-hint">
                        <div class="hint" data-tooltip="Уровень стилизации изображения">i</div>
                        Стилизация (--stylize)            </label>
                    <input type="number" id="stylize_input" name="stylize_input" min="0" max="1000" value="100" @input="updateInput($event.target.value, 'stylize')" class="number-input">
                </div>
                <input type="range" id="stylize" name="stylize" min="0" max="1000" value="100" @input="updateInput($event.target.value, 'stylize_input')" class="full-width-range">
            </div>

            <!-- Weird -->
            <div class="form-group box padding border-radius">
                <div class="label-and-input">
                    <label for="weird" class="label-hint">
                        <div class="hint" data-tooltip="Необычность и оригинальность изображения">i</div>
                        Необычность (--weird)            </label>
                    <input type="number" id="weird_input" name="weird_input" min="0" max="3000" value="0" @input="updateInput($event.target.value, 'weird')" class="number-input">
                </div>
                <input type="range" id="weird" name="weird" min="0" max="3000" value="0" @input="updateInput($event.target.value, 'weird_input')" class="full-width-range">
            </div>

            <!-- Aesthetics -->
            <div id="exp-block" class="form-group box padding border-radius">
                <div class="label-and-input">
                    <label for="exp" class="label-hint">
                        <div class="hint" data-tooltip="Глубина и драма изображения">i</div>
                        Эстетика (--exp)            </label>
                    <input type="number" id="exp_input" name="exp_input" min="1" max="100" value="1" @input="updateInput($event.target.value, 'exp')" class="number-input">
                </div>
                <input type="range" id="exp" name="exp" min="1" max="100" value="1" @input="updateInput($event.target.value, 'exp_input')" class="full-width-range">
            </div>

            <!-- sref -->
            <div id="sref-block" class="form-group box padding border-radius">
                <div>
                    <label for="sref" class="label-hint">
                        <div class="hint" data-tooltip="Ссылка на изображение для использования в качестве стилевого образца">i</div>
                        Референс стиля (--sref)          </label>
                    <input style="padding: 10px !important" type="text" id="sref" name="sref" class="full-width-input" placeholder="Вставьте ссылку" value="">
                </div>

                <!-- sw -->
                <div>
                    <div class="label-and-input">
                        <label for="sw" class="label-hint">
                            <div class="hint" data-tooltip="Настройка опорной силы для параметра --sref">i</div>
                            Опорная сила (--sw)            </label>
                        <input type="number" id="sw_input" name="sw_input" min="0" max="1000" value="0" @input="updateInput($event.target.value, 'sw')" class="number-input" disabled>
                    </div>
                    <input type="range" id="sw" name="sw" min="0" max="1000" value="0" @input="updateInput($event.target.value, 'sw_input')" class="full-width-range" disabled>
                </div>
            </div>

            <!-- cref -->
            <div id="cref-block" class="form-group box padding border-radius">
                <div>
                    <label for="cref" class="label-hint">
                        <div class="hint" data-tooltip="Ссылка(и) на изображение для использования в качестве образца персонажа">i</div>
                        Референс персонажа (--cref)          </label>
                    <input type="text" id="cref" name="cref" class="full-width-input" placeholder="Вставьте ссылку(и)" value="">
                </div>

                <!-- cw -->
                <div>
                    <div class="label-and-input">
                        <label for="cw" class="label-hint">
                            <div class="hint" data-tooltip="Настройка опорной силы для параметра --cref">i</div>
                            Опорная сила (--cw)            </label>
                        <input style="padding: 10px !important" type="number" id="cw_input" name="cw_input" min="0" max="100" value="0" @input="updateInput($event.target.value, 'cw')" class="number-input" disabled>
                    </div>
                    <input type="range" id="cw" name="cw" min="0" max="100" value="0" @input="updateInput($event.target.value, 'cw_input')" class="full-width-range" disabled>
                </div>
            </div>

            <!-- oref -->
            <div id="oref-block" class="form-group box padding border-radius">
                <div>
                    <label for="oref" class="label-hint">
                        <div class="hint" data-tooltip="Ссылка(и) на изображение для использования в качестве образца объекта">i</div>
                        Референс объекта (--oref)          </label>
                    <input style="padding: 10px !important" type="text" id="oref" name="oref" class="full-width-input" placeholder="Вставьте ссылку(и)" value="">
                </div>

                <!-- ow -->
                <div>
                    <div class="label-and-input">
                        <label for="ow" class="label-hint">
                            <div class="hint" data-tooltip="Настройка опорной силы для параметра --oref">i</div>
                            Опорная сила (--ow)            </label>
                        <input type="number" id="ow_input" name="ow_input" min="0" max="1000" value="0" @input="updateInput($event.target.value, 'ow')" class="number-input" disabled>
                    </div>
                    <input style="padding: 10px !important" type="range" id="ow" name="ow" min="0" max="1000" value="0" @input="updateInput($event.target.value, 'ow_input')" class="full-width-range" disabled>
                </div>
            </div>

            <button type="button" class="button-primary" @click="resetResult()">Сбросить параметры</button>
            <button id="prompt-result-btn" type="button" class="button-primary" @click="showPromptResult()">Показать промпт</button>
            <textarea id="prompt-result" name="prompt-result" class="full-width-input" rows="3" style="resize: vertical; min-height: 50px; display: none" readonly @click="this.select()"></textarea>
        </div>
    </section>
</template>

<style scoped>
</style>