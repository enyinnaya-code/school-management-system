<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>{{ school_name() ?? 'SMS' }}</title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="{{ asset('css/app.min.css') }}">

  <!-- Template CSS -->
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('css/components.css') }}">

  <!-- Custom style CSS -->
  <link rel="stylesheet" href="{{ asset('css/custom.css') }}">

  <!-- Dynamic Favicon -->
  <link rel="shortcut icon" type="image/x-icon"
    href="{{ school_logo() ?? asset('images/school_management_logo__1_-removebg-preview.png') }}" />

  <!-- Extra Styles -->
  <link rel="stylesheet" href="{{ asset('style.css') }}">

  <meta name="csrf-token" content="{{ csrf_token() }}">
  <!-- FullCalendar CSS -->
  <link rel="stylesheet" href="{{ asset('bundles/fullcalendar/fullcalendar.min.css') }}">

  <!-- Toastr CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote.min.css" rel="stylesheet">

  <!-- jQuery (required for Summernote) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Summernote JS -->
  <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote.min.js"></script>

  <!-- Tom Select CSS -->
  <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">

  <style>
    .list-unstyled {
      padding-left: 0;
      list-style: none;
      color: black;
      font-size: 1.2rem;
    }

    .dropdown-menu li.divider {
      border-top: 1px solid #9e9d9d;
      margin: 5px 0;
    }

   
  </style>
</head>