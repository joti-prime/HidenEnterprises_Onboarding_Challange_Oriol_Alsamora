class CodeEditor {
    constructor(textareaId) {
        this.editor = CodeMirror.fromTextArea(document.getElementById(textareaId), {
            lineNumbers: true,
            mode: 'properties',
            theme: this.getTheme(),
            extraKeys: {
                "F11": function(cm) {
                    cm.setOption("fullScreen", !cm.getOption("fullScreen"));
                },
                "Ctrl-S": function(cm) {
                    document.getElementById('editor-modal-save-btn').click();
                }
            }
        });
        this.editor.setSize(null, '70vh');

        window.addEventListener('storage', (event) => {
            if (event.key === 'color-theme') {
                this.changeTheme(this.getTheme());
            }
        });
    }

    refresh() {
        this.editor.refresh();
        this.editor.focus();
    }

    reset() {
        this.editor.setValue('');
        this.refresh();
    }

    getValue() {
        return this.editor.getValue();
    }

    setValue(value) {
        if (this.editor.getOption('mode') === 'javascript') {
            try {
                const parsed = JSON.parse(value);
                value = JSON.stringify(parsed, null, 2);
            } catch (e) {
                console.error(e);
            }
        }

        // Перевіряємо, чи завантажено mode
        const mode = this.editor.getOption('mode');
        const modeLoaded = !!document.querySelector(`script[src*="${mode}.min.js"]`);

        if (!modeLoaded) {
            console.log(`Mode ${mode} is not loaded yet. Delaying setValue...`);
            setTimeout(() => this.setValue(value), 100);
        } else {
            this.editor.setValue(value);
        }
        this.refresh();
    }

    // Change syntax mode
    changeMode(fileName, value = null) {
        const mode = this.detectMode(fileName);
        const scriptSrc = `https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.7/mode/${mode}/${mode}.min.js`;

        if (!document.querySelector(`script[src="${scriptSrc}"]`)) {
            const script = document.createElement('script');
            script.src = scriptSrc;
            script.onload = () => {
                this.editor.setOption('mode', mode);
                if (value !== null) {
                    this.setValue(value);
                }
            };
            script.onerror = () => {
                this.editor.setOption('mode', 'properties');
                if (value !== null) {
                    this.setValue(value);
                }
            };
            document.head.appendChild(script);
        } else {
            this.editor.setOption('mode', mode);
            if (value !== null) {
                this.setValue(value);
            }
        }
    }

    // Changing the subject
    changeTheme(theme) {
        this.editor.setOption('theme', theme);
        this.editor.refresh();
    }

    // Toggle full-screen mode
    toggleFullScreen() {
        this.editor.setOption("fullScreen", !this.editor.getOption("fullScreen"));
    }

    getTheme() {
        const dark = document.getElementById('editor-dark-theme').value;
        const light = document.getElementById('editor-light-theme').value;
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia(
            '(prefers-color-scheme: dark)').matches)) {
            return dark;
        } else {
            return light;
        }
    }

    detectMode(fileName, defaultValue = 'properties') {
        const extension = fileName.split('.').pop().toLowerCase();
        switch (extension) {
            case 'js': return 'javascript';
            case 'json': return 'javascript';
            case 'html': return 'htmlmixed';
            case 'htm': return 'htmlmixed';
            case 'md': return 'markdown';
            case 'markdown': return 'markdown';
            case 'c': return 'clike';
            case 'cpp': return 'clike';
            case 'h': return 'clike';
            case 'yml': return 'yaml';
            case 'yaml': return 'yaml';
            case 'py': return 'python';
            case 'java': return 'java';
            case 'rb': return 'ruby';
            case 'sh': return 'shell';
            case 'pl': return 'perl';
            case 'apl': return 'apl';
            case 'asciiarmor': return 'asciiarmor';
            case 'asn1': return 'asn.1';
            case 'asterisk': return 'asterisk';
            case 'brainfuck': return 'brainfuck';
            case 'clike': return 'clike';
            case 'clojure': return 'clojure';
            case 'cmake': return 'cmake';
            case 'cobol': return 'cobol';
            case 'coffeescript': return 'coffeescript';
            case 'commonlisp': return 'commonlisp';
            case 'crystal': return 'crystal';
            case 'css': return 'css';
            case 'cypher': return 'cypher';
            case 'd': return 'd';
            case 'dart': return 'dart';
            case 'diff': return 'diff';
            case 'django': return 'django';
            case 'dockerfile': return 'dockerfile';
            case 'dtd': return 'dtd';
            case 'dylan': return 'dylan';
            case 'ebnf': return 'ebnf';
            case 'ecl': return 'ecl';
            case 'eiffel': return 'eiffel';
            case 'elm': return 'elm';
            case 'erlang': return 'erlang';
            case 'factor': return 'factor';
            case 'fcl': return 'fcl';
            case 'forth': return 'forth';
            case 'fortran': return 'fortran';
            case 'gas': return 'gas';
            case 'gfm': return 'gfm';
            case 'gherkin': return 'gherkin';
            case 'go': return 'go';
            case 'groovy': return 'groovy';
            case 'haml': return 'haml';
            case 'handlebars': return 'handlebars';
            case 'haskell-literate': return 'haskell-literate';
            case 'haskell': return 'haskell';
            case 'haxe': return 'haxe';
            case 'htmlembedded': return 'htmlembedded';
            case 'htmlmixed': return 'htmlmixed';
            case 'http': return 'http';
            case 'idl': return 'idl';
            case 'javascript': return 'javascript';
            case 'jinja2': return 'jinja2';
            case 'jsx': return 'jsx';
            case 'julia': return 'julia';
            case 'livescript': return 'livescript';
            case 'lua': return 'lua';
            case 'mathematica': return 'mathematica';
            case 'mbox': return 'mbox';
            case 'mirc': return 'mirc';
            case 'mllike': return 'mllike';
            case 'modelica': return 'modelica';
            case 'mscgen': return 'mscgen';
            case 'mumps': return 'mumps';
            case 'nginx': return 'nginx';
            case 'nsis': return 'nsis';
            case 'ntriples': return 'ntriples';
            case 'octave': return 'octave';
            case 'oz': return 'oz';
            case 'pascal': return 'pascal';
            case 'pegjs': return 'pegjs';
            case 'perl': return 'perl';
            case 'php': return 'php';
            case 'pig': return 'pig';
            case 'powershell': return 'powershell';
            case 'properties': return 'properties';
            case 'protobuf': return 'protobuf';
            case 'pug': return 'pug';
            case 'puppet': return 'puppet';
            case 'python': return 'python';
            case 'q': return 'q';
            case 'r': return 'r';
            case 'rpm': return 'rpm';
            case 'rst': return 'rst';
            case 'ruby': return 'ruby';
            case 'rust': return 'rust';
            case 'sas': return 'sas';
            case 'sass': return 'sass';
            case 'scheme': return 'scheme';
            case 'shell': return 'shell';
            case 'sieve': return 'sieve';
            case 'slim': return 'slim';
            case 'smalltalk': return 'smalltalk';
            case 'smarty': return 'smarty';
            case 'solr': return 'solr';
            case 'soy': return 'soy';
            case 'sparql': return 'sparql';
            case 'spreadsheet': return 'spreadsheet';
            case 'sql': return 'sql';
            case 'stex': return 'stex';
            case 'stylus': return 'stylus';
            case 'swift': return 'swift';
            case 'tcl': return 'tcl';
            case 'textile': return 'textile';
            case 'tiddlywiki': return 'tiddlywiki';
            case 'tiki': return 'tiki';
            case 'toml': return 'toml';
            case 'tornado': return 'tornado';
            case 'troff': return 'troff';
            case 'ttcn-cfg': return 'ttcn-cfg';
            case 'ttcn': return 'ttcn';
            case 'turtle': return 'turtle';
            case 'twig': return 'twig';
            case 'vb': return 'vb';
            case 'vbscript': return 'vbscript';
            case 'velocity': return 'velocity';
            case 'verilog': return 'verilog';
            case 'vhdl': return 'vhdl';
            case 'vue': return 'vue';
            case 'wast': return 'wast';
            case 'webidl': return 'webidl';
            case 'xml': return 'xml';
            case 'xquery': return 'xquery';
            case 'yacas': return 'yacas';
            case 'yaml-frontmatter': return 'yaml-frontmatter';
            default:
                return defaultValue;
        }
    }

}
