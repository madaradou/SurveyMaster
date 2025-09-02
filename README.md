# SurveyMaster

A web-based survey management system built with PHP that allows users to create, manage, and analyze surveys.

## 🚀 Features

- User authentication and authorization
- Survey creation and management
- Campaign management
- Survey response collection and analysis
- Google Login integration
- Email notification system

## 💻 Technologies

- PHP
- MySQL
- JavaScript
- Composer for dependency management

## 📋 Prerequisites

- PHP >= 7.4
- MySQL >= 5.7
- Composer
- Web server (Apache/Nginx)

## ⚙️ Installation

1. Clone the repository
```bash
git clone https://github.com/yourusername/surveymaster.git
```

2. Navigate to the project directory
```bash
cd surveymaster
```

3. Install dependencies
```bash
composer install
```

4. Configure your database settings in `config/config.php`

5. Import the SQL files from the `sql/` directory to set up your database schema

## 🛠️ Configuration

1. Set up Google Login credentials in `config/google_config.php`
2. Configure mail settings in `config/mail_config.php`

## 📁 Project Structure

```
├── assets/          # Static assets (images, css)
├── config/          # Configuration files
├── controllers/     # Application controllers
├── models/          # Database models
├── js/             # JavaScript files
├── sql/            # Database schemas
└── vendor/         # Composer dependencies
```

## 👥 Contributing

1. Fork the project
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details

## ✍️ Authors

- Your Name - Initial work

## 🙏 Acknowledgments

- Hat tip to anyone whose code was used
- Inspiration
- etc
