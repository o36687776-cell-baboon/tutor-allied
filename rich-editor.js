// Simple Rich Text Editor
class RichEditor {
    constructor(textareaId) {
        this.textareaId = textareaId;
        this.textarea = document.getElementById(textareaId);
        if (!this.textarea) {
            console.error('Textarea not found:', textareaId);
            return;
        }

        this.editorId = textareaId + '_editor';
        this.createEditor();
        this.createToolbar();
        this.init();
    }

    createEditor() {
        // Create a contentEditable div to replace the textarea
        const editor = document.createElement('div');
        editor.id = this.editorId;
        editor.className = 'rich-editor-content';
        editor.contentEditable = true;
        editor.style.minHeight = '200px';
        editor.style.border = '1px solid #ccc';
        editor.style.padding = '10px';
        editor.style.borderRadius = '4px';
        editor.innerHTML = this.textarea.value || '<p><br></p>';

        // Hide the original textarea
        this.textarea.style.display = 'none';

        // Insert editor before textarea
        this.textarea.parentNode.insertBefore(editor, this.textarea);
    }

    createToolbar() {
        const toolbar = document.createElement('div');
        toolbar.className = 'rich-toolbar mb-2 d-flex gap-1';
        toolbar.innerHTML = `
            <button type="button" class="btn btn-sm btn-outline-secondary" data-command="bold" title="Bold"><i class="fas fa-bold"></i></button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-command="italic" title="Italic"><i class="fas fa-italic"></i></button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-command="underline" title="Underline"><i class="fas fa-underline"></i></button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-command="insertUnorderedList" title="Bullet List"><i class="fas fa-list-ul"></i></button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-command="insertOrderedList" title="Numbered List"><i class="fas fa-list-ol"></i></button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertLink('${this.editorId}')" title="Insert Link"><i class="fas fa-link"></i></button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertImage('${this.editorId}')" title="Insert Image"><i class="fas fa-image"></i></button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-command="undo" title="Undo"><i class="fas fa-undo"></i></button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-command="redo" title="Redo"><i class="fas fa-redo"></i></button>
        `;

        // Insert toolbar before the editor
        const editor = document.getElementById(this.editorId);
        editor.parentNode.insertBefore(toolbar, editor);

        // Add event listeners to toolbar buttons
        toolbar.querySelectorAll('button[data-command]').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const command = button.getAttribute('data-command');
                document.execCommand(command, false, null);
                editor.focus();
            });
        });
    }

    init() {
        const editor = document.getElementById(this.editorId);

        // Sync content back to textarea for form submission
        editor.addEventListener('input', () => {
            this.textarea.value = editor.innerHTML;
        });

        editor.addEventListener('keyup', () => {
            this.textarea.value = editor.innerHTML;
        });

        editor.addEventListener('paste', () => {
            setTimeout(() => {
                this.textarea.value = editor.innerHTML;
            }, 100);
        });

        // Ensure editor stays focused and maintains content
        editor.addEventListener('blur', () => {
            if (editor.innerHTML.trim() === '') {
                editor.innerHTML = '<p><br></p>';
            }
        });

        editor.addEventListener('focus', () => {
            if (editor.innerHTML.trim() === '<p><br></p>') {
                editor.innerHTML = '';
            }
        });
    }
}

// Global functions for toolbar
function insertLink(editorId) {
    const url = prompt('Enter URL:');
    if (url) {
        const editor = document.getElementById(editorId);
        editor.focus();
        document.execCommand('createLink', false, url);
    }
}

function insertImage(editorId) {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const editor = document.getElementById(editorId);
                editor.focus();
                document.execCommand('insertImage', false, event.target.result);
            };
            reader.readAsDataURL(file);
        }
    };
    input.click();
}

// Initialize editors on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize rich editors for all textareas with class 'rich-editor'
    const textareas = document.querySelectorAll('textarea.rich-editor');
    textareas.forEach(textarea => {
        if (textarea.id) {
            new RichEditor(textarea.id);
        }
    });
});