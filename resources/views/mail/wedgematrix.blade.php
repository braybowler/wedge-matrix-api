/* Base */

body,
body *:not(html):not(style):not(br):not(tr):not(code) {
    box-sizing: border-box;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif,
        'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
    position: relative;
}

body {
    -webkit-text-size-adjust: none;
    background-color: #111827;
    color: #9ca3af;
    height: 100%;
    line-height: 1.4;
    margin: 0;
    padding: 0;
    width: 100% !important;
}

p,
ul,
ol,
blockquote {
    line-height: 1.4;
    text-align: left;
}

a {
    color: #818cf8;
}

a img {
    border: none;
}

/* Typography */

h1 {
    color: #f3f4f6;
    font-size: 18px;
    font-weight: bold;
    margin-top: 0;
    text-align: left;
}

h2 {
    color: #f3f4f6;
    font-size: 16px;
    font-weight: bold;
    margin-top: 0;
    text-align: left;
}

h3 {
    color: #f3f4f6;
    font-size: 14px;
    font-weight: bold;
    margin-top: 0;
    text-align: left;
}

p {
    color: #9ca3af;
    font-size: 16px;
    line-height: 1.5em;
    margin-top: 0;
    text-align: left;
}

p.sub {
    font-size: 12px;
}

img {
    max-width: 100%;
}

/* Layout */

.wrapper {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 100%;
    background-color: #111827;
    margin: 0;
    padding: 0;
    width: 100%;
}

.content {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 100%;
    margin: 0;
    padding: 0;
    width: 100%;
}

/* Header */

.header {
    padding: 25px 0;
    text-align: center;
}

.header a {
    color: #818cf8;
    font-size: 19px;
    font-weight: bold;
    text-decoration: none;
}

/* Logo */

.logo {
    height: 75px;
    max-height: 75px;
    width: 75px;
}

/* Body */

.body {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 100%;
    background-color: #111827;
    border-bottom: 1px solid #111827;
    border-top: 1px solid #111827;
    margin: 0;
    padding: 0;
    width: 100%;
}

.inner-body {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 570px;
    background-color: #1f2937;
    border-color: #4b5563;
    border-radius: 8px;
    border-width: 1px;
    box-shadow: 0 2px 0 rgba(0, 0, 0, 0.1), 2px 4px 0 rgba(0, 0, 0, 0.05);
    margin: 0 auto;
    padding: 0;
    width: 570px;
}

.inner-body a {
    word-break: break-all;
}

/* Subcopy */

.subcopy {
    border-top: 1px solid #4b5563;
    margin-top: 25px;
    padding-top: 25px;
}

.subcopy p {
    font-size: 14px;
}

/* Footer */

.footer {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 570px;
    margin: 0 auto;
    padding: 0;
    text-align: center;
    width: 570px;
}

.footer p {
    color: #6b7280;
    font-size: 12px;
    text-align: center;
}

.footer a {
    color: #6b7280;
    text-decoration: underline;
}

/* Tables */

.table table {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 100%;
    margin: 30px auto;
    width: 100%;
}

.table th {
    border-bottom: 1px solid #4b5563;
    color: #f3f4f6;
    margin: 0;
    padding-bottom: 8px;
}

.table td {
    color: #9ca3af;
    font-size: 15px;
    line-height: 18px;
    margin: 0;
    padding: 10px 0;
}

.content-cell {
    max-width: 100vw;
    padding: 32px;
}

/* Buttons */

.action {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 100%;
    margin: 30px auto;
    padding: 0;
    text-align: center;
    width: 100%;
    float: unset;
}

.button {
    -webkit-text-size-adjust: none;
    border-radius: 8px;
    color: #fff;
    display: inline-block;
    overflow: hidden;
    text-decoration: none;
}

.button-blue,
.button-primary {
    background-color: #818cf8;
    border-bottom: 8px solid #818cf8;
    border-left: 18px solid #818cf8;
    border-right: 18px solid #818cf8;
    border-top: 8px solid #818cf8;
}

.button-green,
.button-success {
    background-color: #48bb78;
    border-bottom: 8px solid #48bb78;
    border-left: 18px solid #48bb78;
    border-right: 18px solid #48bb78;
    border-top: 8px solid #48bb78;
}

.button-red,
.button-error {
    background-color: #ef4444;
    border-bottom: 8px solid #ef4444;
    border-left: 18px solid #ef4444;
    border-right: 18px solid #ef4444;
    border-top: 8px solid #ef4444;
}

/* Panels */

.panel {
    border-left: #818cf8 solid 4px;
    margin: 21px 0;
}

.panel-content {
    background-color: #374151;
    color: #9ca3af;
    padding: 16px;
}

.panel-content p {
    color: #9ca3af;
}

.panel-item {
    padding: 0;
}

.panel-item p:last-of-type {
    margin-bottom: 0;
    padding-bottom: 0;
}

/* Utilities */

.break-all {
    word-break: break-all;
}
