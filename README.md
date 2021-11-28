<div align="center">
    <a href="https://ceramic.eu.org">
        <img alt="Ceramic" src="https://img.static.sgnetworks.eu.org/logos/Ceramic-Cup.png" width="150">
    </a>
</div>


# The Ceramic Framework
Ceramic is a lightweight yet powerful Model-View-Controller (MVC) framework for PHP. Ceramic is mostly compatible with [CodeIgniter](https://github.com/bcit-ci/CodeIgniter), which includes the
"Helpers", "Libraries" and also the configuration system almost same as CodeIgniter yet easy to configure and use, which makes it an iconic PHP MVC framework. It allows ***Ceramic Developers*** to use
the power of two frameworks in one. Ceramic is released under its exclusive license [CMF License v1.0](https://github.com/SGNetworksIndia/Ceramic/blob/master/LICENSE).


## REQUIREMENTS
* **Ceramic Version**: 1.1.0


### Core Requirements
| Technology | Version        | Link                                                                   |
|------------|----------------|------------------------------------------------------------------------|
| PHP        | 8.1.0          | [www.php.net/downloads](https://www.php.net/downloads/)                |
| MySQL      | 8.0.11+        | [downloads.mysql.com](https://downloads.mysql.com/archives/community/) |


### CodeIgniter Compatibility
| Version (Minimum) | Version (Maximum) | Link                                                          |
|-------------------|-------------------|---------------------------------------------------------------|
| 4.1.4             | 4.1.5             | [CI 4](https://github.com/codeigniter4/CodeIgniter4/releases) |


## INSTALLATION
Download the latest version of Ceramic from
[ceramic.eu.org/downloads](https://ceramic.eu.org/downloads/) or
[github.com/SGNetworksIndia/Ceramic/releases](https://github.com/SGNetworksIndia/Ceramic/releases), and extract the archive on the root directory of your website, or you may extract it on any
directory you want.

Now open `/application/` directory and change the `config.php` & `database.php` according to your requirement but the `base_url` variable must be changed according to your relative project root.

Now you are ready to start developing your website in Ceramic, to start developing, just write your codes and create or put the files in the `/application/` directory. For more information, read
the [documentation](#documentation).


## DOCUMENTATION
The documentation for **_Ceramic_** is available at [docs.ceramic.eu.org](https://docs.ceramic.eu.org/) and the documentation for **_CodeIgniter_** can be found
at [codeigniter.com/user_guide](https://codeigniter.com/user_guide/index.html).


## CODEIGNITER HELPERS & LIBRARIES
To install and use CodeIgniter Helpers and Libraries, just copy the helper or library from a compatible CodeIgniter release package to Ceramic (`/system/`) while matching the path.

* ### To install helpers:
  Copy the helper from CodeIgniter to `Ceramic/system/helpers/` and access the helper from **Controller** using `$loader->load->helper('helper_name')`

* ### To install libraries:
  Copy the library from CodeIgniter to `Ceramic/system/libraries/` (matching the exact path it was on CodeIgniter) and access the helper from **Controller** using `$loader->load->library
  ('library_name')`


**_Support for CodeIgniter `Session` library has been dropped from the release of `Ceramic v1.1.5`, as a replacement for the library `Ceramic` now have its own `Session` library. with other storage
related libraries. The `Session` library can be instantiated by calling `$this->load->library('Storage/Session/Session')` from a Controller. See
`/application/controllers/Demo::captcha()` for more details._**


## UPCOMING FEATURES
There are many features are staged to be implemented in the upcoming releases. The currently planned features awaiting to be implemented are:

1. Router
2. RESTful Resource Handling
3. Improvement on Templating feature (add support for if...else blocks, loops, etc.)
4. Hooks
5. Services (background php classes) & Web Services
6. Caching
7. Asynchronous Requests (AJAX)
8. HTTP/2 Server Push (Server Sent Events)
9. Localization


## CONTRIBUTING
To contribute on the **_Ceramic Core_**, send an email on [contribute@ceramic.eu.org](mailto:contribute@ceramic.eu.org) with the following information:

* Your real name
* Your GitHub Username
* Your contact E-mail ID (where you can be reached)
* Describing your skills and an idea on how the upcoming features can be implemented

If you are selected, you'll receive an email from the core development team with all the information required to move forward.

Or if you have a new feature which can be added in the `Ceramic Core`, just email to [rfc@ceramic.eu.org](mailto:rfc@ceramic.eu.org) with your proposal.

Or if you found a bug or error which need to be fixed create an issue at [issues](https://github.com/SGNetworksIndia/Ceramic/issues) or if you think you can fix the issue, report the bug to
[bug@ceramic.eu.org](mailto:bug@ceramic.eu.org) explaining the issue you found.


## CREDITS
**_Ceramic_ is being developed by _Team Ceramic_ in association with _[SGNetworks](https://github.com/SGNetworksIndia/)_ and _[Indiosco Technologies Private Limited](https://github.com/Indiosco/)_.**


* ### Team Ceramic
  **Founder, Project Head & Lead Developer:** [Sagnik Ganguly](https://github.com/SagnikGanguly96) (SGN)

  **Project Manager & Coordinator:** [Pallab Mukherjee](https://github.com/Pallab-Mukherjee) (ITPL)



