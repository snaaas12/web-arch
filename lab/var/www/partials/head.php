<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boardy - Доска объявлений</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
        }
        
        nav {
            background: #1A5276;
            padding: 1rem 2rem;
            display: flex;
            gap: 1.5rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        nav a, nav span {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }
        
        nav .brand {
            font-size: 1.5rem;
            font-weight: bold;
            margin-right: 1rem;
        }
        
        nav a:hover {
            text-decoration: underline;
        }
        
        nav span {
            margin-left: auto;
        }
        
        main {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        h1 {
            margin-bottom: 1.5rem;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #555;
        }
        
        input, textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        button {
            background: #1A5276;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
        }
        
        button:hover {
            background: #2c6e9e;
        }
        
        .error {
            background: #fee;
            color: #c00;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .post {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .post-author {
            color: #1A5276;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .post-body {
            color: #333;
            line-height: 1.4;
        }
        
        .post-date {
            color: #999;
            font-size: 0.8rem;
            margin-top: 0.5rem;
        }
        
        .link {
            color: #1A5276;
            text-decoration: none;
        }
        
        .link:hover {
            text-decoration: underline;
        }
        
        hr {
            margin: 1rem 0;
            border: none;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
