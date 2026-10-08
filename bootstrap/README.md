# PI Bootstrap - AI Academy Incubation Hub

A fully functional Bootstrap-based Single Page Application (SPA) for the PI Network AI Academy incubation platform.

## Features

### 🎯 Core Functionality
- **Product Type Selection**: Choose between Community, Enterprise, and Individual products
- **Project Directory**: Browse and contribute to projects filtered by type
- **Live Code IDE**: Write, run, and debug HTML, CSS, and JavaScript code
- **Badge System**: Earn badges for contributions
- **Staking Simulation**: Mock staking functionality

### 💻 IDE Features (Sololearn-style)
- **Multi-language Support**: HTML, CSS, JavaScript
- **Live Preview**: Real-time rendering of HTML/CSS
- **Console Output**: JavaScript execution with logging
- **Syntax Debugging**: Code validation and error checking
- **Example Loading**: Pre-built code examples for learning

### 🔧 Technical Stack
- **Frontend**: Vanilla JavaScript, Bootstrap 5, HTML5, CSS3
- **Backend**: Client-side storage (localStorage)
- **Server**: Apache/XAMPP compatible
- **Database**: Optional MySQL with Docker
- **Containerization**: Docker Compose for services

## Installation

### Option 1: Local Development (XAMPP/Windows)
1. Clone or download files to `C:/xampp/htdocs/bootstrap/`
2. Start XAMPP Apache and MySQL
3. Open `http://localhost/bootstrap/` in browser

### Option 2: Production Server (Linux/Ubuntu)
1. Upload files to your web server
2. Run the setup script:
   ```bash
   chmod +x setup.sh
   sudo ./setup.sh
   ```
3. Access at `http://your-domain.com`

### Option 3: Docker Environment
```bash
docker-compose up -d
```

## Project Structure

```
pi-bootstrap/
├── index.html          # Main HTML file
├── css/
│   └── theme.css       # Styling
├── js/
│   ├── app.js          # Application initialization
│   ├── router.js       # Hash-based routing
│   ├── popup.js        # Consent modal
│   ├── selection.js    # Product type selection
│   ├── directory.js    # Project directory
│   ├── ide.js          # Code playground
│   ├── badges.js       # Badge system
│   └── util.js         # Utilities
├── data/
│   └── projects.json   # Project data
├── webhook/
│   └── server.js       # GitHub webhook handler
├── docker-compose.yml  # Docker services
├── setup.sh           # Environment setup script
└── README.md          # This file
```

## Usage

1. **Landing Page**: Click "Enter Bootstrap" to begin
2. **Consent**: Agree to terms to continue
3. **Product Selection**: Choose Community, Enterprise, or Individual
4. **Main Interface**:
   - **Home**: Welcome and quick links
   - **Directory**: Browse projects by selected type
   - **IDE**: Code playground with live preview
   - **Badges**: View earned achievements
   - **Stakes**: Mock staking interface

## IDE Usage

### HTML Mode
- Write complete HTML documents
- Includes live preview in iframe
- Supports embedded CSS and JavaScript

### CSS Mode
- Write CSS stylesheets
- Preview applied to test HTML structure
- Debug checks for syntax errors

### JavaScript Mode
- Write and execute JavaScript code
- Console output with color-coded logs
- Syntax validation and error reporting

## Configuration

### Database (Optional)
The app works entirely client-side, but Docker provides:
- MySQL database on port 3307
- phpMyAdmin on port 8081
- Node.js webhook server on port 4001

### SSL Setup
```bash
sudo certbot --apache -d your-domain.com
```

## Development

### Adding New Projects
Edit `data/projects.json`:
```json
{
  "id": "new-project",
  "title": "Project Name",
  "summary": "Brief description",
  "long": "Detailed description",
  "type": "community|enterprise|individual",
  "owner": "username",
  "repo": "https://github.com/user/repo"
}
```

### Customizing Styling
Edit `css/theme.css` for theme modifications.

### Extending Functionality
- Add new routes in `js/router.js`
- Create new page functions following existing patterns
- Extend storage utilities in `js/util.js`

## Security Notes

- Client-side storage only (no server-side data persistence)
- No user authentication required
- Docker services include default passwords (change in production)
- SSL recommended for production deployment

## Browser Support

- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## License

This project is part of the PI Network AI Academy incubation program.

## Support

For issues or contributions, please refer to the PI Network community channels.