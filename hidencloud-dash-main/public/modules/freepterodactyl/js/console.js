// CommandManager Class
class CommandManager {
    constructor(orderId, consoleManager) {
        this.orderId = orderId;
        this.consoleManager = consoleManager;

        this.commandHistory = [];
        this.historyIndex = 0;
        this.favoriteCommands = [];
        this.recommendedCommands = [];

        // Initialize methods
        this.init();
    }

    init() {
        // Load command history
        this.commandHistory = this.getCommandHistory();
        this.historyIndex = this.commandHistory.length;

        // Fetch favorite and recommended commands
        this.fetchFavoriteCommands().then(() => {
            // Fetch recommended commands after favorites to ensure favorite status is accurate
            this.fetchRecommendedCommands();
        });

        // Add event listeners
        this.addEventListeners();
    }

    addEventListeners() {
        // Open Modal button
        const openModalButton = document.getElementById('open-modal-button');
        if (openModalButton) {
            openModalButton.addEventListener('click', () => this.openCommandModal('history'));
        }

        // Close Modal button
        const closeModalButton = document.getElementById('close-modal');
        if (closeModalButton) {
            closeModalButton.addEventListener('click', () => this.closeCommandModal());
        }

        // Modal background click to close
        const commandModal = document.getElementById('command-modal');
        if (commandModal) {
            commandModal.addEventListener('click', (event) => {
                if (event.target === commandModal) {
                    this.closeCommandModal();
                }
            });
        }

        // Tab buttons in the modal
        const historyTabButton = document.getElementById('history-tab');
        const recommendedTabButton = document.getElementById('recommended-tab');

        if (historyTabButton) {
            historyTabButton.addEventListener('click', () => this.switchModalTab('history'));
        }

        if (recommendedTabButton) {
            recommendedTabButton.addEventListener('click', () => this.switchModalTab('recommended'));
        }
    }

    // Modal methods
    openCommandModal(tab = 'history') {
        const modal = document.getElementById('command-modal');
        if (modal) {
            modal.classList.remove('hidden');
            this.switchModalTab(tab);
        }
    }

    closeCommandModal() {
        const modal = document.getElementById('command-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    switchModalTab(tab) {
        const historyTabButton = document.getElementById('history-tab');
        const recommendedTabButton = document.getElementById('recommended-tab');
        const historyContent = document.getElementById('history-content');
        const recommendedContent = document.getElementById('recommended-content');

        if (tab === 'history') {
            historyTabButton.classList.add('bg-gray-200', 'dark:bg-gray-700');
            historyTabButton.classList.remove('bg-gray-100', 'dark:bg-gray-600');
            recommendedTabButton.classList.remove('bg-gray-200', 'dark:bg-gray-700');
            recommendedTabButton.classList.add('bg-gray-100', 'dark:bg-gray-600');
            historyContent.classList.remove('hidden');
            recommendedContent.classList.add('hidden');
            this.displayCommandHistory();
        } else if (tab === 'recommended') {
            recommendedTabButton.classList.add('bg-gray-200', 'dark:bg-gray-700');
            recommendedTabButton.classList.remove('bg-gray-100', 'dark:bg-gray-600');
            historyTabButton.classList.remove('bg-gray-200', 'dark:bg-gray-700');
            historyTabButton.classList.add('bg-gray-100', 'dark:bg-gray-600');
            recommendedContent.classList.remove('hidden');
            historyContent.classList.add('hidden');
            this.displayRecommendedCommands();
        }
    }

    // Command History methods
    getCommandHistory() {
        const historyKey = `commandHistory_${this.orderId}`;
        let history = localStorage.getItem(historyKey);
        return history ? JSON.parse(history) : [];
    }

    saveCommandToHistory(command) {
        const historyKey = `commandHistory_${this.orderId}`;
        let history = this.getCommandHistory();
        const index = history.indexOf(command);
        if (index > -1) {
            history.splice(index, 1);
        }
        history.push(command);
        localStorage.setItem(historyKey, JSON.stringify(history));
    }

    displayCommandHistory() {
        const historyList = document.getElementById('history-list');
        if (!historyList) return;
        historyList.innerHTML = '';

        if (this.commandHistory.length === 0) {
            const emptyMessage = document.createElement('p');
            emptyMessage.className = 'text-sm text-gray-500 dark:text-gray-400';
            historyList.appendChild(emptyMessage);
            return;
        }

        this.commandHistory.slice().reverse().forEach(command => {
            const commandButton = this.createCommandButton(command, {
                onExecute: () => {
                    this.consoleManager.sendCommand(command);
                    this.closeCommandModal();
                },
            });

            historyList.appendChild(commandButton);
        });
    }

    // Favorite Commands methods
    fetchFavoriteCommands() {
        return fetch(`/service-panel/${this.orderId}/console/commands`, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }, credentials: 'same-origin',
        })
            .then(response => response.json())
            .then(data => {
                this.favoriteCommands = data;
                this.displayFavoriteCommands();
            })
            .catch(error => console.error('Error fetching favorite commands:', error));
    }

    saveFavoriteCommands() {
        fetch(`/service-panel/${this.orderId}/console/commands`, {
            method: 'POST', headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }, credentials: 'same-origin', body: JSON.stringify({commands: this.favoriteCommands}),
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log(data.message);
                }
            })
            .catch(error => console.error('Error saving favorite commands:', error));
    }

    displayFavoriteCommands() {
        const favoriteCommandsContainer = document.getElementById('favorite-commands');
        if (!favoriteCommandsContainer) return;
        favoriteCommandsContainer.innerHTML = '';

        this.favoriteCommands.forEach(command => {
            const commandButton = this.createFavoriteCommandButton(command, {
                onExecute: () => {
                    this.consoleManager.sendCommand(command);
                }, onDelete: () => {
                    this.removeCommandFromFavorites(command);
                },
            });

            favoriteCommandsContainer.appendChild(commandButton);
        });
    }

    addCommandToFavorites(command) {
        if (!this.favoriteCommands.includes(command)) {
            this.favoriteCommands.push(command);
            this.saveFavoriteCommands();
            this.displayFavoriteCommands();
        }
    }

    removeCommandFromFavorites(command) {
        const index = this.favoriteCommands.indexOf(command);
        if (index > -1) {
            this.favoriteCommands.splice(index, 1);
            this.saveFavoriteCommands();
            this.displayFavoriteCommands();
        }
    }

    // Recommended Commands methods
    fetchRecommendedCommands() {
        fetch(`/service-freepterodactyl/${this.orderId}/console/recommended`, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }, credentials: 'same-origin',
        })
            .then(response => response.json())
            .then(data => {
                this.recommendedCommands = data;
                this.displayRecommendedCommands(); // Ensure commands are displayed after fetching
            })
            .catch(error => console.error('Error fetching recommended commands:', error));
    }

    displayRecommendedCommands() {
        const recommendedList = document.getElementById('recommended-list');
        if (!recommendedList) return;
        recommendedList.innerHTML = '';

        if (this.recommendedCommands.length === 0) {
            const emptyMessage = document.createElement('p');
            emptyMessage.className = 'text-sm text-gray-500 dark:text-gray-400';
            recommendedList.appendChild(emptyMessage);
            return;
        }

        this.recommendedCommands.forEach(command => {
            const commandButton = this.createCommandButton(command, {
                onExecute: () => {
                    this.consoleManager.sendCommand(command);
                    this.closeCommandModal();
                },
            });

            recommendedList.appendChild(commandButton);
        });
    }

    // Helper methods to create command buttons
    createCommandButton(command, options = {}) {
        const {onExecute} = options;

        // Create the button element
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white font-semibold py-1 px-2 rounded flex items-center justify-between w-full';
        button.style.minWidth = '150px'; // Adjust width as needed

        // Add click event to execute the command
        button.addEventListener('click', () => {
            if (typeof onExecute === 'function') {
                onExecute();
            }
        });

        // Use flexbox to position elements
        const contentWrapper = document.createElement('div');
        contentWrapper.className = 'flex items-center justify-between w-full';

        const textSpan = document.createElement('span');
        textSpan.textContent = command;

        contentWrapper.appendChild(textSpan);

        // Add the favorite icon
        const isFavorite = this.favoriteCommands.includes(command);
        const favoriteIcon = document.createElement('span');
        favoriteIcon.className = 'cursor-pointer ml-2';
        favoriteIcon.innerHTML = isFavorite ? '❤️' : '🤍'; // Use filled heart for favorite

        favoriteIcon.addEventListener('click', (event) => {
            event.stopPropagation(); // Prevent button click event
            this.toggleFavoriteStatus(command, favoriteIcon);
        });

        contentWrapper.appendChild(favoriteIcon);

        button.appendChild(contentWrapper);

        return button;
    }

    toggleFavoriteStatus(command, iconElement) {
        if (this.favoriteCommands.includes(command)) {
            this.removeCommandFromFavorites(command);
            iconElement.innerHTML = '🤍';
        } else {
            this.addCommandToFavorites(command);
            iconElement.innerHTML = '❤️';
        }
    }

    createFavoriteCommandButton(command, options = {}) {
        const {onExecute, onDelete} = options;

        // Create the button element
        const button = document.createElement('button');
        button.className = 'bg-primary-700 hover:bg-primary-800 text-white font-semibold py-1 px-2 rounded-lg focus:outline-none flex items-center justify-between w-auto';
        button.style.minWidth = '150px'; // Adjust width as needed

        // Add click event to execute the command
        button.addEventListener('click', () => {
            if (typeof onExecute === 'function') {
                onExecute();
            }
        });

        // Create the text span
        const textSpan = document.createElement('span');
        textSpan.textContent = command;

        // Create the delete icon
        const deleteIcon = document.createElement('span');
        deleteIcon.className = 'text-red-500 hover:text-red-700 cursor-pointer ml-2 opacity-0 transition-opacity duration-200';
        deleteIcon.innerHTML = '❌';

        deleteIcon.addEventListener('click', (event) => {
            event.stopPropagation(); // Prevent triggering command execution
            if (typeof onDelete === 'function') {
                onDelete();
            }
        });

        // Show delete icon on hover
        button.addEventListener('mouseenter', () => {
            deleteIcon.style.opacity = '1';
        });

        button.addEventListener('mouseleave', () => {
            deleteIcon.style.opacity = '0';
        });

        // Use flexbox to position the delete icon on the right
        const contentWrapper = document.createElement('div');
        contentWrapper.className = 'flex items-center justify-between w-full';

        contentWrapper.appendChild(textSpan);
        contentWrapper.appendChild(deleteIcon);

        button.appendChild(contentWrapper);

        return button;
    }
}


// ConsoleManager Class
class ConsoleManager {
    constructor() {
        // Initialize properties
        this.buttons = ['start', 'restart', 'stop', 'kill'];
        this.statusIndicator = document.getElementById('statusIndicator');
        this.statusText = document.getElementById('statusText');
        this.consoleOutput = document.getElementById('console-output');
        this.input = document.getElementById('commandInput');
        this.cpuUsageText = document.querySelector('.cpu-usage-info .cpu-usage');
        this.cpuUsageBar = document.querySelector('.cpu-usage-info .cpu-usage-bar');
        this.memoryUsageText = document.querySelector('.memory-usage-info .memory-usage');
        this.memoryUsageBar = document.querySelector('.memory-usage-info .memory-usage-bar');
        this.diskUsageText = document.querySelector('.disk-usage-info .disk-usage');
        this.diskUsageBar = document.querySelector('.disk-usage-info .disk-usage-bar');
        this.totalMemory = parseFloat(document.getElementById('totalMemory').value);
        this.totalDisk = parseFloat(document.getElementById('totalDisk').value);
        this.totalCPU = parseFloat(document.getElementById('totalCPU').value);
        this.orderId = document.getElementById('orderId').value;
        this.socketUrl = document.getElementById('socketUrl').value;
        this.ansi_up = new AnsiUp();
        this.commandButtons = document.querySelectorAll('button[data-command]');
        this.translations = {
            starting: document.getElementById('translate-starting').textContent,
            stopping: document.getElementById('translate-stopping').textContent,
            running: document.getElementById('translate-running').textContent,
            offline: document.getElementById('translate-offline').textContent,
            suspended: document.getElementById('translate-suspended').textContent,
            installing: document.getElementById('translate-installing').textContent,
            updating: document.getElementById('translate-updating').textContent,
        };

        this.socket = null;

        // Initialize CommandManager
        this.commandManager = new CommandManager(this.orderId, this);

        // Initialize methods
        this.init();
    }

    init() {
        // Add event listeners
        this.addEventListeners();

        // Initialize WebSocket
        this.initializeWebSocket();
    }

    addEventListeners() {
        // Event handler for command input field
        this.input.addEventListener('keydown', (event) => this.handleInput(event));

        // Event handlers for command buttons
        this.commandButtons.forEach(button => {
            button.addEventListener('click', () => {
                const command = button.getAttribute('data-command');
                this.sendCommand(command);
            });
        });

        // Server control buttons
        this.buttons.forEach(buttonId => {
            const button = document.getElementById(buttonId);
            if (button) {
                button.addEventListener('click', () => {
                    this.setServerState(buttonId);
                });
            }
        });
    }

    // WebSocket methods
    initializeWebSocket() {
        fetch(this.socketUrl, {
            headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
        })
            .then(response => response.json())
            .then(data => {
                this.socket = new WebSocket(data.socket);
                this.socket.onopen = () => this.authenticate(data);
                this.socket.onmessage = (event) => this.handleWebSocketMessage(event);
                this.socket.onerror = error => console.error('WebSocket Error: ', error);
            })
            .catch(error => console.error('Error fetching websocket data:', error));
    }

    authenticate(data) {
        if (this.socket) {
            this.socket.send(JSON.stringify({'event': 'auth', 'args': [data.token]}));
        }
    }

    handleWebSocketMessage(event) {
        const message = JSON.parse(event.data);
        switch (message.event) {
            case 'install output':
                this.addConsoleOutput(message.args[0]);
                this.updateButtonStates('installing');
                this.updateServerStatus('installing');
                break;
            case 'install completed':
                this.initializeWebSocket();
                break;
            case 'console output':
                this.addConsoleOutput(message.args[0]);
                break;
            case 'status':
                this.updateButtonStates(message.args[0]);
                this.updateServerStatus(message.args[0]);
                break;
            case 'stats':
                const data = JSON.parse(message.args[0]);
                this.updateButtonStates(data.state);
                this.updateServerStatus(data.state);
                this.updateResourceUsage(data);
                break;
            case 'token expiring':
                this.fetchNewToken();
                break;
            case 'auth success':
                this.socket.send(JSON.stringify({'event': 'send logs', 'args': [null]}));
                break;
        }
    }

    fetchNewToken() {
        fetch(this.socketUrl, {
            headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
        })
            .then(response => response.json())
            .then(data => this.authenticate(data))
            .catch(error => console.error('Error fetching new websocket token:', error));
    }

    // Console output methods
    addConsoleOutput(text) {
        const newLine = document.createElement('p');
        newLine.innerHTML = this.ansi_up.ansi_to_html(this.consoleReplace(text));
        this.consoleOutput.appendChild(newLine);
        this.consoleOutput.scrollTop = this.consoleOutput.scrollHeight;
    }

    consoleReplace(text) {
        return text
            .replace('=>....', '')
            .replace('>....', '')
            .replace('[FreePterodactyl Daemon]:', '')
            .replace('container@freepterodactyl~', '');
    }

    // Server status and resource usage updates
    updateButtonStates(status) {
        const isInstallDisabled = status === 'installing';
        const isStartDisabled = status === 'running' || status === 'starting' || isInstallDisabled;
        const isOtherDisabled = status === 'offline' || status === 'stopping' || isInstallDisabled;

        this.buttons.forEach(buttonId => {
            const button = document.getElementById(buttonId);
            if (!button || buttonId === 'kill') return;

            if (isInstallDisabled) {
                button.disabled = true;
            } else {
                if (buttonId === 'start') {
                    button.disabled = isStartDisabled;
                } else {
                    button.disabled = isOtherDisabled;
                }
            }
        });
    }

    updateServerStatus(status) {
        let colorClass = '';
        let translationsStatus = this.statusText.textContent;

        switch (status) {
            case 'offline':
                colorClass = 'bg-red-600';
                translationsStatus = this.translations.offline;
                break;
            case 'running':
                colorClass = 'bg-emerald-600';
                translationsStatus = this.translations.running;
                break;
            case 'starting':
                colorClass = 'bg-orange-600';
                translationsStatus = this.translations.starting;
                break;
            case 'stopping':
                colorClass = 'bg-yellow-600';
                translationsStatus = this.translations.stopping;
                break;
            case 'installing':
                colorClass = 'bg-orange-600';
                translationsStatus = this.translations.installing;
                break;
            case 'suspended':
                colorClass = 'bg-gray-600';
                translationsStatus = this.translations.suspended;
                break;
            case 'updating':
                colorClass = 'bg-purple-600';
                translationsStatus = this.translations.updating;
                break;
            default:
                colorClass = 'bg-gray-600';
        }

        if (this.statusIndicator) {
            this.statusIndicator.className = `flex w-4 h-4 ${colorClass} rounded-full mr-1.5 flex-shrink-0`;
        }
        if (this.statusText) {
            this.statusText.textContent = translationsStatus;
        }
    }

    updateResourceUsage(data) {
        if (typeof data === 'string') {
            data = JSON.parse(data);
        }

        // Update resource display
        const cpuPercent = data.cpu_absolute;
        const memoryUsed = data.memory_bytes / 1024 / 1024; // Conversion to MB
        const diskUsed = data.disk_bytes / 1024 / 1024; // Conversion to MB

        // CPU usage
        let cpuUsageDisplay;
        let cpuUsagePercent;

        if (this.totalCPU === 0) {
            cpuUsageDisplay = `${cpuPercent.toFixed(2)}% / ∞`;
            cpuUsagePercent = Math.min(cpuPercent, 100);
        } else {
            cpuUsageDisplay = `${cpuPercent.toFixed(2)}% / ${this.totalCPU}%`;
            cpuUsagePercent = Math.min((cpuPercent / this.totalCPU) * 100, 100);
        }

        this.cpuUsageText.textContent = cpuUsageDisplay;
        this.cpuUsageBar.style.width = `${cpuUsagePercent.toFixed(2)}%`;

        // Memory usage
        let memoryUsageDisplay;
        let memoryUsagePercent;

        if (this.totalMemory === 0) {
            memoryUsageDisplay = `${memoryUsed.toFixed(2)} MB / ∞`;
            memoryUsagePercent = 0;
        } else {
            memoryUsageDisplay = `${memoryUsed.toFixed(2)} MB / ${this.totalMemory} MB`;
            memoryUsagePercent = Math.min((memoryUsed / this.totalMemory) * 100, 100);
        }

        this.memoryUsageText.textContent = memoryUsageDisplay;
        this.memoryUsageBar.style.width = `${memoryUsagePercent.toFixed(2)}%`;

        // Disk usage
        let diskUsageDisplay;
        let diskUsagePercent;

        if (this.totalDisk === 0) {
            diskUsageDisplay = `${diskUsed.toFixed(2)} MB / ∞`;
            diskUsagePercent = 0;
        } else {
            diskUsageDisplay = `${diskUsed.toFixed(2)} MB / ${this.totalDisk} MB`;
            diskUsagePercent = Math.min((diskUsed / this.totalDisk) * 100, 100);
        }

        this.diskUsageText.textContent = diskUsageDisplay;
        this.diskUsageBar.style.width = `${diskUsagePercent.toFixed(2)}%`;
    }

    // Input handling
    handleInput(event) {
        if (event.key === 'Enter' && this.input.value) {
            this.sendCommand(this.input.value.replace(/\//g, ''));
            this.input.value = '';
        } else if (event.key === 'ArrowUp') {
            this.commandManager.historyIndex = Math.max(this.commandManager.historyIndex - 1, 0);
            this.input.value = this.commandManager.commandHistory[this.commandManager.historyIndex] || '';
            event.preventDefault();
        } else if (event.key === 'ArrowDown') {
            this.commandManager.historyIndex = Math.min(this.commandManager.historyIndex + 1, this.commandManager.commandHistory.length);
            this.input.value = this.commandManager.commandHistory[this.commandManager.historyIndex] || '';
            event.preventDefault();
        }
    }

    // Command sending
    sendCommand(command) {
        if (this.socket) {
            this.socket.send(JSON.stringify({'event': 'send command', 'args': [command]}));
        }
        this.commandManager.saveCommandToHistory(command);
    }

    setServerState(action) {
        if (this.socket) {
            this.socket.send(JSON.stringify({'event': 'set state', 'args': [action]}));
        }
    }
}

// Create an instance of ConsoleManager
const consoleManager = new ConsoleManager();
