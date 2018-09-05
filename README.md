## Let’s Build a Project Time Tracker with Symfony and VueJS
![time-tracker](https://user-images.githubusercontent.com/19610753/45070841-4490f380-b0cb-11e8-8efc-61b22f62753a.jpeg)

Build a Project Time Tracker with Symfony and VueJS. 

See the screenshot below:


![symfony-time](https://user-images.githubusercontent.com/19610753/45070890-83bf4480-b0cb-11e8-9a55-becc5aa376a2.png)
![tracker](https://user-images.githubusercontent.com/19610753/45070891-8457db00-b0cb-11e8-8f2d-8f464496e885.png)


## Link to tutorial
[Here](https://www.cloudways.com/blog/time-tracking-system-php-symfony-vue/)



## Getting Started

### Clone the repository
```bash
$ git clone https://github.com/yemiwebby/time-tracker-app.git
```

### Change directory
```bash
$ cd time-tracker-app
```

### Use composer to manage and install dependencies
```bash
$ composer install
```

### Install the frontend dependencies
Run the command below from the project's root directory in another terminal:

```bash
$ yarn install
```

Do ensure that you have two separate terminals opened on your machine. One of them will be used to start the Symfony app while the other will keep the frontend running.

### Start the application
#### Backend
```bash
$ php bin/console server:run
```

#### Frontend
```bash
 $ yarn run encore dev --watch
```

## Prerequisites
A basic knowledge of JavaScript and Object oriented programming with PHP will help you get the best out of this article. Do ensure that you have [Node.js](https://nodejs.org/en/) and [Yarn package manager](https://yarnpkg.com/lang/en/docs/install/#mac-stable) installed on your system.

## Built With

* [Symfony](https://symfony.com/) - Is a set of reusable PHP components
* [Vue.js](https://vuejs.org/) - A progressive JavaScript framework for building user interfaces.
* [Webpack Encore](https://github.com/symfony/webpack-encore) - Webpack Encore is a simpler way to integrate Webpack into your application.