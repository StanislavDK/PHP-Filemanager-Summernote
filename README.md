# PHP Filemanager Summernote
Lightweight AJAX (PHP, JQuery) File Manager for Summernote

This script is a lightweight and simple File Manager plugin for the Summernote. Written in php using bootstrap and jquery. 
It is based on scripts https://github.com/jcampbell1/simple-file-manager, http://github.com/jamiebicknell/Thumb, https://github.com/zpalffy/preview-image-jquery, https://github.com/gumlet/php-image-resize/.

Features: 
- Lightweight, simple;
- Responsive design and usability;
- Download files;
- Resize images depending on the destination folder; 
- Preview downloaded images;
- Delete, rename files;
- Search file;
- Create folder;
- Password and CSRF protection; 

![Summernote filemanager](https://github.com/StanislavDK/PHPFilemanager4Summernote/blob/main/2.png "Орк")
![Summernote filemanager](https://github.com/StanislavDK/PHPFilemanager4Summernote/blob/main/1.png "Орк")


Installation:

1. On the page where the summernote is called:
  ```html
  <script src="/summernote/plugins/filebrowser/filemanager.js"></script>

  <div id="summernote"><p>Hello Summernote</p></div>
```
  ```javascript
  <script>
    $(document).ready(function() {
        $('#summernote').summernote({
        toolbar: [
          ['style', ['style']],
          ['style', ['bold', 'italic', 'underline', 'clear']],
          ['font', ['strikethrough', 'superscript', 'subscript']],
          ['color', ['color']],
          ['insert', ['link', 'video', 'table','filebrowser', 'hr']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['undo', ['undo', 'redo']],
          ['codeview', ['codeview']],
        ]
      });
    });
  </script>
 ``` 
2. In the /summernote/plugins/filebrowser/filemanager.js, you must change the name of the element summernote (in example "#summernote").
3. In the /summernote/plugins/filebrowser/filemanager.php:
```php
  $disallowed_extensions - array with forbidden extensions
  $allowed_extensions - array with allowed extensions
  $images_extensions - array with image extensions
  $allowed_types - array with allowed mime-type
  $custom_folder - an array with individual parameters for images of a specific folder (size, prefix, the ability to create new folders) 
  $PASSWORD - if set, a password will be requested
  $base_dir - directory for download
```  
 Optional:
```html 
 <iframe width=100% height=450px style="border:0" id="iframe" src="/manager/plugins/summernote/plugins/filebrowser/filemanager.php?folder=prices&subfolder='+    Price_Name +'&namefile='+ NameFile +'&returnid=price_setting  " id="eframe" class="eframe"></iframe>
```  
 returnid - returned item id #
 
 In the /summernote/plugins/filebrowser/filemanager.php:
 ```php
 $custom_prices = array("folder" => "/prices", "prefix" => "pr", "size" => "200", "foldercreate" => "yes"); 
  ```
When uploading a file to the "prices" directory, the prefix "pr" will be added to the file name, image size will be reduced to 200 px, it is possible to create a new folder.
  
 Attention! This script was created for a project, support is limited (3 years ago). 
  
