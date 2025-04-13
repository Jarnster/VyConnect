# VyConnect - VyOS Multi-Router UI Controller

This is one of my first open-source releases. Please add a ⭐ if you like the idea of this project or if you like to use it.

Feel free to contribute.

VyConnect is an open-source web interface for managing multiple VyOS routers via the official VyOS REST API instead of SSH ensuring safe connectivity without compromising on security. It provides a centralized way to configure and monitor multiple VyOS routers, with features for NAT, firewalls, interfaces, logging, performance monitoring, and more. The project aims to simplify multi-router management, providing a more integrated and user-friendly approach than managing routers individually via CLI.

---

![image](https://github.com/user-attachments/assets/57082baf-7422-4e9f-b1ae-a66566dd371d)

## 🚀 Installation

Follow the steps below to set up VyConnect and connect it to your VyOS router through the REST API.

### 1. **Set up the VyOS REST API**

On your VyOS router, enable the REST API with the following commands in the VyOS CLI:

```bash
set service https api keys id vyconnect key YOUR_SECRET_KEY
set service https api rest
commit && save
```

Replace `YOUR_SECRET_KEY` with your own chosen secret key. This is used to securely communicate between VyConnect and your VyOS router.

### 2. **Configure Ethernet Interfaces, Speed, and Duplex**

Ensure your Ethernet interfaces are properly configured with the correct speed and duplex settings.

### 3. **Copy and Edit the Configuration File**

Copy `default.config.json` to `config.json` and modify the values to suit your setup. Be sure to include the correct router settings and REST API credentials.

### 4. **Access and Default Password**

Use PHP to serve /web. If you are using Windows, you can open host_web.cmd, which runs the webpanel. Make sure you have got a recent version of PHP installed on your host system!

The default password for VyConnect is `vyconnect`. Use this to log into the web interface.

---

## ⚙️ Features (TODO)

### Basic Features:
- **NAT & Firewall Configuration**: Easily manage NAT and firewall settings.
- **Adding Interfaces**: Add network interfaces without needing to use the CLI.
- **Setup Wizard**: A simple setup wizard for installing specific features.
- **Performance & Statistics**: View performance metrics and statistics for your routers.
- **Logging**: Manage and view router logs.
- **Multiple User Accounts**: Support for multiple administrator accounts.
- **Security Alerts**: Alerts for potential security issues.
- **REST API Back-end**: Periodically send HTTP requests to VyOS and store the responses.

### Advanced Features:
- **Automatic Recommendations**: Based on collected statistics, automatic configuration recommendations may be made.
- **Unit Tests for VyOS Features**: Automatically test the stability of VyOS features in rolling/stable releases.

### Multi-Router Integrations:
- **Router Group Management**: Manage multiple VyOS routers in groups.
- **Global Configuration Application**: Apply configuration changes across multiple routers or groups.
- **Configuration Comparison**: Compare configurations between router groups.
- **Performance Comparison**: Compare performance metrics between different router groups.

---

## 🌐 Documentation

For more details on the VyOS REST API and related configuration, refer to the following resources:

- [VyOS API Documentation](https://docs.vyos.io/en/latest/automation/vyos-api.html)
- [VyOS HTTPS Service Configuration](https://docs.vyos.io/en/latest/configuration/service/https.html)

---

## ⚠️ Notes

Make sure your VyOS router is configured correctly and the REST API is enabled to ensure smooth operation with VyConnect.

---

VyConnect is an open-source project focused on simplifying the management of multiple VyOS routers. Feel free to contribute or make improvements as you see fit.

---
