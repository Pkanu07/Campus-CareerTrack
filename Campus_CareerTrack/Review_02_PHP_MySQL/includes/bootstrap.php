<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_set_cookie_params(['httponly'=>true,'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off','samesite'=>'Lax']); session_start(); }
require_once __DIR__.'/../config/database.php';
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function csrf_token(){ if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_check(){ if($_SERVER['REQUEST_METHOD']==='POST' && !hash_equals($_SESSION['csrf']??'', $_POST['csrf']??'')){ http_response_code(419); exit('Invalid or expired form token. Go back and try again.'); } }
function require_login(){ if(empty($_SESSION['user'])){ header('Location: ../auth/login.php'); exit; } }
function require_admin(){ require_login(); if(($_SESSION['user']['role']??'')!=='admin'){ http_response_code(403); exit('Access denied. Admin account required.'); } }
function flash($message,$type='success'){ $_SESSION['flash']=['message'=>$message,'type'=>$type]; }
function show_flash(){ if(!empty($_SESSION['flash'])){ $f=$_SESSION['flash']; echo '<div class="notice '.e($f['type']).'">'.e($f['message']).'</div>'; unset($_SESSION['flash']); } }
function header_html($title='Campus CareerTrack'){ $u=$_SESSION['user']??null; echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.e($title).'</title><link rel="stylesheet" href="../assets/app.css"></head><body><header class="topbar"><a class="brand" href="../index.php">Campus <span>CareerTrack</span></a><nav><a href="../index.php">Home</a>'; if($u){ echo '<a href="../student/dashboard.php">Dashboard</a>'; if($u['role']==='admin') echo '<a href="../admin/opportunities.php">Manage roles</a>'; echo '<span class="welcome">'.e($u['name']).'</span><a href="../auth/logout.php">Logout</a>'; } else echo '<a href="../auth/login.php">Login</a><a href="../auth/register.php">Register</a>'; echo '</nav></header><main class="container">'; show_flash(); }
function footer_html(){ echo '</main><footer>Campus CareerTrack · Student Placement & Internship Management System</footer></body></html>'; }
