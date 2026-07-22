class FileManager {
    constructor(config) {
        this.checkedFiles = [];
        this.currentPath = config.currentPath;
        this.currentPathElement = config.currentPathElement;
        this.fileUrl = config.fileUrl;
        this.downloadUrl = config.downloadUrl;
        this.renameUrl = config.renameUrl;
        this.copyUrl = config.copyUrl;
        this.deleteUrl = config.deleteUrl;
        this.compressUrl = config.compressUrl;
        this.decompressUrl = config.decompressUrl;
        this.writeUrl = config.writeUrl;
        this.uploadUrlRequest = config.uploadUrl;
        this.contentUrl = config.contentUrl;
        this.createDirectoryUrl = config.createDirectoryUrl;
        this.editor = config.editor;
        this.token = config.token;
        this.tableBody = document.getElementById('table-body');
        this.contextMenu = new ContextMenu(document.getElementById('contextMenu'), this);
        this.translateActions = config.translateActions;
        this.uploadProcessBar = new UploadProcessBar();
        this.doubleClick = config.doubleClick === '1';
        this.backButton = document.getElementById('back-btn');
        this.preloader = document.getElementById('preloader');
        this.initialize();
    }

    async initialize() {
        const files = await this.fetchFiles(this.currentPath);
        this.updateTable(files);
        this.initializeEventHandlers();

    }

    showPreloader() {
        this.preloader.classList.remove('hidden');
    }

    hidePreloader() {
        this.preloader.classList.add('hidden');
    }

    initializeEventHandlers() {
        document.getElementById('new_directory_submit').addEventListener('click', async () => {
            await this.createDirectory();
            const files = await this.fetchFiles(this.currentPath);
            this.updateTable(files);
        });
        document.getElementById('editor-modal-cancel-btn').addEventListener('click', async () => {
            this.editor.reset();
        });
        document.getElementById('new-file-modal-btn').addEventListener('click', async () => {
            this.editor.reset();
            this.openEditModal(null);
        });
        document.getElementById('editor-modal-save-btn').addEventListener('click', async () => {
            const content = this.editor.getValue();
            if (content.trim() !== '') {
                const fileName = document.getElementById('editor_file_name').value;
                await this.writeToFile(fileName, content);
                const files = await this.fetchFiles(this.currentPath);
                this.updateTable(files);
            }
        });
        document.getElementById('editor-modal-save-and-close-btn').addEventListener('click', async () => {
            const content = this.editor.getValue();
            if (content.trim() !== '') {
                const fileName = document.getElementById('editor_file_name').value;
                await this.writeToFile(fileName, content);
                const files = await this.fetchFiles(this.currentPath);
                this.updateTable(files);
            }
        });
        document.addEventListener('click', (event) => {
            if (!event.target.closest('#contextMenu')) {
                this.contextMenu.hide();
            }
        });
        document.getElementById('checkbox-all-search').addEventListener('change', (event) => {
            document.querySelectorAll('.checkbox-row').forEach(checkbox => {
                checkbox.checked = event.target.checked;
                const fileData = JSON.parse(checkbox.getAttribute('data-checkbox-file'));
                if (checkbox.checked) {
                    if (!this.checkedFiles.some(checkedFile => checkedFile.name === fileData.name)) {
                        this.checkedFiles.push(fileData);
                    }
                } else {
                    this.checkedFiles = this.checkedFiles.filter(checkedFile => checkedFile.name !== fileData.name);
                }
            });
        });
        document.getElementById('upload-modal-submit').addEventListener('click', async () => {
            const files = document.getElementById('upload-modal-input').files;
            if (files.length > 0) {
                const uploadedFiles = await this.uploadFiles(files);
                if (uploadedFiles) {
                    const files = await this.fetchFiles(this.currentPath);
                    this.updateTable(files);
                }
            }
        });
        this.backButton.addEventListener('click', async () => {
            this.navigateUp();
        });

    }

    async navigateUp() {
        if (this.currentPath !== '/') {
            this.showPreloader();
            this.currentPath = this.currentPath.substring(0, this.currentPath.lastIndexOf('/', this.currentPath.length - 2) + 1);
            this.currentPathElement.textContent = this.currentPath;
            const files = await this.fetchFiles(this.currentPath);
            this.updateTable(files);
            this.hidePreloader();
        }
    }

    updateTable(files) {
        this.tableBody.innerHTML = '';
        if (this.currentPath !== '/') {
            this.backButton.classList.remove('hidden');
        } else {
            this.backButton.classList.add('hidden');
        }

        files.forEach(file => {
            this.tableBody.appendChild(this.createFileRow(file));
        });

        document.getElementById('checkbox-all-search').checked = false;
        this.checkedFiles = [];
        this.contextMenu.hide();
        this.contextMenu.addContextMenuHandlers();
    }

    createFileRow(file) {
        const tr = document.createElement('tr');
        tr.className = 'file_block bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer';
        tr.setAttribute('data-file', JSON.stringify(file));

        tr.appendChild(this.createCheckboxCell(file));
        tr.appendChild(this.createNameCell(file));
        tr.appendChild(this.createSizeCell(file));
        tr.appendChild(this.createModifiedDateCell(file));
        tr.appendChild(this.createMenuButtonCell());

        return tr;
    }

    createCheckboxCell(file) {
        const td = document.createElement('td');
        td.className = 'ps-3 pe-0';
        const div = document.createElement('div');
        div.className = 'flex items-center';
        const input = document.createElement('input');
        input.type = 'checkbox';
        input.className = 'checkbox-row w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600';
        input.setAttribute('data-checkbox-file', JSON.stringify(file));
        input.addEventListener('change', (event) => {
            file = JSON.parse(event.target.getAttribute('data-checkbox-file'));
            if (event.target.checked) {
                if (!this.checkedFiles.some(checkedFile => checkedFile.name === file.name)) {
                    this.checkedFiles.push(file);
                }
            } else {
                this.checkedFiles = this.checkedFiles.filter(checkedFile => checkedFile.name !== file.name);
            }
        });

        const label = document.createElement('label');
        label.className = 'sr-only';
        label.textContent = 'checkbox';

        div.appendChild(input);
        div.appendChild(label);
        td.appendChild(div);
        return td;
    }

    createNameCell(file) {
        const td = document.createElement('td');
        td.className = 'px-6 py-4 ps-0 font-medium text-gray-900 whitespace-nowrap dark:text-white';
        const icon = document.createElement('i');
        icon.className = file.is_file ? 'bx bxs-file' : 'bx bxs-folder';
        td.appendChild(icon);
        td.append(' ' + file.name);

        const event = this.doubleClick ? 'dblclick' : 'click';

        if (!file.is_file) {
            td.addEventListener(event, () => {
                this.currentPath += file.name + '/';
                this.currentPathElement.textContent = this.currentPath;
                this.fetchFiles(this.currentPath).then(files => {
                    this.updateTable(files);
                });
            });
        } else {
            td.addEventListener(event, () => {
                this.getFileContent(file).then(response => {
                    if (typeof response === 'string') {

                        this.openEditModal(file, response);
                    }
                });
            });
        }
        return td;
    }

    createSizeCell(file) {
        const td = document.createElement('td');
        td.className = 'px-6 py-4 text-end';
        if (file.is_file) {
            td.textContent = file.size;
        }
        return td;
    }

    createModifiedDateCell(file) {
        const td = document.createElement('td');
        td.className = 'text-end pe-0';
        td.textContent = file.modified_at;
        return td;
    }

    createMenuButtonCell() {
        const td = document.createElement('td');
        td.className = 'pe-4 text-end';
        const button = document.createElement('button');
        button.className = 'context-menu-button';
        button.innerHTML = "<i class='bx bx-menu'></i>";
        td.appendChild(button);
        return td;
    }

    openEditModal(file = null, value = null) {
        const fileName = document.getElementById('editor_file_name');
        const modalBtn = document.getElementById('edit-modal-btn');
        if (file) {
            fileName.value = file.name;
            fileName.disabled = true;
            this.editor.changeMode(file.name, value);
            this.editor.refresh();
        } else {
            this.editor.setValue('');
            fileName.disabled = false;
            fileName.value = 'new-file-name.json';
        }
        modalBtn.click();
        this.editor.refresh();
    }

    async createDirectory() {
        this.showPreloader();
        let name = document.getElementById('new_directory_name').value;
        if (name && name.trim() !== '') {
            const params = new URLSearchParams({path: this.currentPath, name: name});
            const urlWithParams = `${this.createDirectoryUrl}?${params.toString()}`;
            try {
                await fetch(urlWithParams, {method: 'GET'});
            } catch (error) {
                console.error('Error:', error);
            } finally {
                this.hidePreloader();
            }
        }
    }

    async submitRename(file, newName) {
        if (newName && newName.trim() !== '') {
            const params = new URLSearchParams({path: this.currentPath, new_name: newName, old_name: file.name});
            const urlWithParams = `${this.renameUrl}?${params.toString()}`;
            try {
                await fetch(urlWithParams, {method: 'GET'});
                const files = await this.fetchFiles(this.currentPath);
                this.updateTable(files);
            } catch (error) {
                console.error('Error:', error);
            }
        }
    }

    async copyFile(file) {
        const params = new URLSearchParams({path: this.currentPath + file.name});
        const urlWithParams = `${this.copyUrl}?${params.toString()}`;
        try {
            await fetch(urlWithParams, {method: 'GET'});
            const files = await this.fetchFiles(this.currentPath);
            this.updateTable(files);
        } catch (error) {
            console.error('Error:', error);
        }
    }

    async deleteFile(files) {
        confirmModal(this.translateActions['ConfirmDelete']).then(async () => {
            try {
                await fetch(this.deleteUrl, {
                    method: 'DELETE', headers: {
                        'Content-Type': 'application/json',
                    }, body: JSON.stringify({_token: this.token, path: this.currentPath, files: files})
                });
                const updatedFiles = await this.fetchFiles(this.currentPath);
                this.updateTable(updatedFiles);
            } catch (error) {
                console.error('Error:', error);
            }
        });
    }

    async downloadFile(file) {
        const params = new URLSearchParams({path: this.currentPath + file.name});
        const urlWithParams = `${this.downloadUrl}?${params.toString()}`;
        try {
            const response = await fetch(urlWithParams, {method: 'GET'});
            const file = await response.json();
            window.location.href = file.url;
        } catch (error) {
            console.error('Error:', error);
        }
    }

    async archiveFiles(files) {
        try {
            await fetch(this.compressUrl, {
                method: 'POST',
                headers: {'Content-Type': 'application/json',},
                body: JSON.stringify({_token: this.token, path: this.currentPath, files: files})
            });
            const updatedFiles = await this.fetchFiles(this.currentPath);
            this.updateTable(updatedFiles);
        } catch (error) {
            console.error('Error:', error);
        }
    }

    async unarchiveFile(file) {
        const params = new URLSearchParams({file_path: this.currentPath, file_name: file.name});
        const urlWithParams = `${this.decompressUrl}?${params.toString()}`;
        try {
            await fetch(urlWithParams, {method: 'GET'});
            const files = await this.fetchFiles(this.currentPath);
            this.updateTable(files);
        } catch (error) {
            console.error('Error:', error);
        }
    }

    async getFileContent(file) {
        const params = new URLSearchParams({file_path: this.currentPath + file.name});
        const urlWithParams = `${this.contentUrl}?${params.toString()}`;
        this.showPreloader();
        try {
            const response = await fetch(urlWithParams, {
                method: 'GET'
            });
            if (response.ok) {
                this.hidePreloader();
                return await response.text();
            }
        } catch (error) {
            console.error('This file is not editable: ' + file.name);
            return null;
        } finally {
            this.hidePreloader();
        }
    }

    async writeToFile(file_name, content) {
        const params = new URLSearchParams({file_path: this.currentPath + file_name});
        const urlWithParams = `${this.writeUrl}?${params.toString()}`;
        try {
            await fetch(urlWithParams, {
                method: 'POST',
                headers: {'Content-Type': 'application/json',},
                body: JSON.stringify({_token: this.token, content: content})
            });
        } catch (error) {
            console.error('Error:', error);
        }
    }

    async fetchFiles(path) {
        this.showPreloader();
        try {
            const response = await fetch(this.fileUrl, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({path: path, _token: this.token})
            });
            if (response.ok) {
                return await response.json();
            }
        } catch (error) {
            console.error('There was a problem with the fetch operation:', error);
            return null;
        } finally {
            this.hidePreloader();
        }
    }

    async getSignedUploadUrl() {
        try {
            const response = await fetch(this.uploadUrlRequest, {method: 'GET'});
            if (response.ok) {
                const data = await response.json();
                return data.url;
            }
        } catch (error) {
            console.error('There was a problem with the fetch operation:', error);
        }
    }

    async uploadFiles(files) {
        this.uploadProcessBar.showUploadStatus();
        const uploadPromises = Array.from(files).map((file, i) => {
            return new Promise(async (resolve, reject) => {
                const signedUrl = await this.getSignedUploadUrl();
                const formData = new FormData();
                formData.append('files', files[i], file.name);
                const fileProcess = this.uploadProcessBar.addFileUploadProcess(file.name);

                const xhr = new XMLHttpRequest();
                xhr.open('POST', signedUrl + '&directory=' + encodeURIComponent(this.currentPath), true);
                xhr.upload.onprogress = (event) => {
                    if (event.lengthComputable) {
                        const percentComplete = (event.loaded / event.total) * 100;
                        this.uploadProcessBar.updateFileUploadProcess(fileProcess, percentComplete.toFixed(2));
                    }
                };
                xhr.onload = async () => {
                    this.uploadProcessBar.completeFileUploadProcess();
                    if (xhr.status === 200) {
                        const updatedFiles = await this.fetchFiles(this.currentPath);
                        this.updateTable(updatedFiles);
                        resolve(xhr.response);
                    } else {
                        console.error(`Error uploading file ${file.name}`);
                        reject(`Error uploading file ${file.name}`);
                    }
                };
                xhr.onerror = () => {
                    this.uploadProcessBar.completeFileUploadProcess();
                    console.error('There was a problem with the fetch operation for file ' + file.name);
                    reject('There was a problem with the fetch operation for file ' + file.name);
                };
                xhr.send(formData);
            });
        });

        await Promise.all(uploadPromises);
        const fetchedFiles = await this.fetchFiles(this.currentPath);
        this.updateTable(fetchedFiles);
    }
}


class UploadProcessBar {
    constructor() {
        this.uploadStatus = document.getElementById('upload-status');
        this.fileUploadList = document.getElementById('file-upload-list');
        this.activeUploads = 0;
    }

    showUploadStatus() {
        this.uploadStatus.classList.remove('hidden');
    }

    hideUploadStatus() {
        if (this.activeUploads === 0) {
            this.uploadStatus.classList.add('hidden');
            this.clearFileUploadList();
        }
    }

    clearFileUploadList() {
        this.fileUploadList.innerHTML = '';
    }

    updateFileUploadProcess(fileProcess, percentage) {
        fileProcess.progressBar.style.width = `${percentage}%`;
    }

    completeFileUploadProcess() {
        this.activeUploads--;
        if (this.activeUploads === 0) {
            this.hideUploadStatus();
        }
    }

    addFileUploadProcess(fileName) {
        this.activeUploads++;

        const fileProcessContainer = document.createElement('div');
        fileProcessContainer.classList.add('mb-4');

        const fileNameElement = document.createElement('div');
        fileNameElement.classList.add('mb-1', 'text-xs', 'font-medium', 'dark:text-white');
        fileNameElement.textContent = fileName;

        const fileProcessBar = document.createElement('div');
        fileProcessBar.classList.add('w-full', 'bg-gray-200', 'rounded-full', 'h-1.5', 'dark:bg-gray-700');

        const fileProgressBarInner = document.createElement('div');
        fileProgressBarInner.classList.add('bg-primary-600', 'text-xs', 'font-medium', 'text-primary-100', 'text-center', 'h-1.5', 'leading-none', 'rounded-full');
        fileProgressBarInner.style.width = '0%';

        fileProcessBar.appendChild(fileProgressBarInner);
        fileProcessContainer.appendChild(fileNameElement);
        fileProcessContainer.appendChild(fileProcessBar);
        this.fileUploadList.appendChild(fileProcessContainer);

        return {
            fileName: fileName,
            progressBar: fileProgressBarInner
        };
    }

}


class ContextMenu {
    constructor(element, fileManager) {
        this.menuElement = element;
        this.fileManager = fileManager;
        this.fileData = null;
    }

    hide() {
        this.menuElement.classList.add('hidden');
    }

    show(x, y, fileData = null) {
        this.fileData = fileData;
        this.createMenu(fileData);
        this.menuElement.style.top = `${y}px`;
        this.menuElement.style.left = `${x}px`;
        this.menuElement.classList.remove('hidden');
    }

    createMenu(fileData) {
        this.menuElement.innerHTML = '';
        const menuList = document.createElement('ul');
        menuList.className = 'text-gray-700 dark:text-gray-200';

        const menuItems = ['Rename', 'Copy', 'Delete', 'Download', 'Archive', 'Unarchive'];
        menuItems.forEach(item => {
            const li = this.createMenuItem(item, fileData);
            if (li) menuList.appendChild(li);
        });
        this.menuElement.appendChild(menuList);
    }

    createMenuItem(item, fileData) {
        const li = document.createElement('li');
        li.className = 'px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer';
        li.innerHTML = this.fileManager.translateActions[item];
        const archiveMimeTypes = ['application/gzip', 'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed', 'application/x-tar'];
        switch (item) {
            case 'Download':
                if (!fileData.is_file || this.fileManager.checkedFiles.length > 0) {
                    return null;
                }
                li.addEventListener('click', () => this.fileManager.downloadFile(fileData));
                break;
            case 'Unarchive':
                if (!fileData.is_file || !archiveMimeTypes.includes(fileData.mimetype) || this.fileManager.checkedFiles.length > 0) {
                    return null;
                }
                li.addEventListener('click', () => this.fileManager.unarchiveFile(fileData));
                break;
            case 'Archive':
                if (archiveMimeTypes.includes(fileData.mimetype)) {
                    return null;
                }
                if (this.fileManager.checkedFiles.length > 0) {
                    let files = this.fileManager.checkedFiles.map(file => file.name);
                    li.addEventListener('click', () => this.fileManager.archiveFiles(files));
                } else {
                    let files = [fileData.name];
                    li.addEventListener('click', () => this.fileManager.archiveFiles(files));
                }
                break;
            case 'Rename':
                if (this.fileManager.checkedFiles.length > 0) {
                    return null;
                }
                li.addEventListener('click', () => this.openRenameModal(fileData));
                break;
            case 'Copy':
                if (!fileData.is_file || this.fileManager.checkedFiles.length > 0) {
                    return null;
                }
                li.addEventListener('click', () => this.fileManager.copyFile(fileData));
                break;
            case 'Delete':
                if (this.fileManager.checkedFiles.length > 0) {
                    let files = this.fileManager.checkedFiles.map(file => file.name);
                    li.addEventListener('click', () => this.fileManager.deleteFile(files));
                } else {
                    let files = [fileData.name];
                    li.addEventListener('click', () => this.fileManager.deleteFile(files));
                }

                break;
        }
        return li;
    }

    openRenameModal(file) {
        const modalBtn = document.getElementById('rename-file-btn');
        const submitBtn = document.getElementById('rename_submit');
        let input = document.getElementById('new_file_name');
        input.value = file.name;

        submitBtn.removeEventListener('click', this.renameSubmitBtnClickListener);
        input.removeEventListener('keydown', this.inputKeydownListener);

        this.inputKeydownListener = (event) => {
            if (event.key === 'Enter') {
                submitBtn.click();
            }
        };
        this.renameSubmitBtnClickListener = () => {
            this.fileManager.submitRename(file, input.value);
        };
        modalBtn.click();
        submitBtn.addEventListener('click', this.renameSubmitBtnClickListener);
        input.addEventListener('keydown', this.inputKeydownListener);
    }

    addContextMenuHandlers() {
        document.querySelectorAll('.file_block').forEach(block => {
            block.addEventListener('contextmenu', (event) => {
                event.preventDefault();
                const fileDataAttr = block.getAttribute('data-file');
                if (fileDataAttr && fileDataAttr.trim() !== '') {
                    const fileData = JSON.parse(fileDataAttr);
                    this.show(event.pageX, event.pageY, fileData);
                }
            });
        });

        document.querySelectorAll('.context-menu-button').forEach(button => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                const block = button.closest('.file_block');
                const fileData = JSON.parse(block.getAttribute('data-file'));
                const x = button.getBoundingClientRect().left;
                const y = button.getBoundingClientRect().bottom + window.scrollY;
                this.show(x, y, fileData);
            });
        });
    }
}


document.addEventListener('DOMContentLoaded', () => {
    const fileManagerConfig = {
        currentPath: '/',
        currentPathElement: document.getElementById('current_path'),
        fileUrl: document.getElementById('fileUrl').value,
        downloadUrl: document.getElementById('downloadUrl').value,
        renameUrl: document.getElementById('renameUrl').value,
        copyUrl: document.getElementById('copyUrl').value,
        deleteUrl: document.getElementById('deleteUrl').value,
        decompressUrl: document.getElementById('decompressUrl').value,
        compressUrl: document.getElementById('compressUrl').value,
        writeUrl: document.getElementById('writeUrl').value,
        uploadUrl: document.getElementById('uploadUrl').value,
        contentUrl: document.getElementById('contentUrl').value,
        createDirectoryUrl: document.getElementById('createDirectory').value,
        token: document.getElementById('csrf_token').value,
        editor: new CodeEditor('editorArea'),
        doubleClick: document.getElementById('doubleClick').value,
        translateActions: {
            'Rename': document.getElementById('translate-Rename').innerHTML,
            'Copy': document.getElementById('translate-Copy').innerHTML,
            'Delete': document.getElementById('translate-Delete').innerHTML,
            'Download': document.getElementById('translate-Download').innerHTML,
            'Archive': document.getElementById('translate-Archive').innerHTML,
            'Unarchive': document.getElementById('translate-Unarchive').innerHTML,
            'ConfirmDelete': document.getElementById('translate-confirm-delete').innerHTML,
        },
    };
    const fileManager = new FileManager(fileManagerConfig);
});
