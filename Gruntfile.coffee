sass = require 'node-sass'

module.exports = (grunt) ->
    # Project configuration.
    grunt.initConfig
        resourcesPath: 'app/Resources'
        npmPath: 'node_modules'
        assetsPath: 'web'

        # Cleans up folders we will later copy to
        clean: [
            '<%= assetsPath %>/partials'
            '<%= assetsPath %>/css'
            '<%= assetsPath %>/images/sprite.svg'
            '<%= assetsPath %>/js'
            '<%= assetsPath %>/fonts'
        ]
        # Copies stuff that needs to be copied
        copy:
            appTemplates:
                expand: true
                cwd: '<%= resourcesPath %>/views/frontend'
                src: ['**/*.html']
                dest: '<%= assetsPath %>/partials'
            bootstrapFonts:
                expand: true
                cwd: '<%= npmPath %>/bootstrap-sass/assets/fonts/bootstrap/'
                src: '*'
                dest: '<%= assetsPath %>/fonts'
            ngTagsInput:
                expand: true
                cwd: '<%= npmPath %>/ng-tags-input/build/'
                src: 'ng-tags-input.bootstrap.min.css'
                dest: '<%= assetsPath %>/css'
        # Compiles JS from coffee, enables Node.js module support
        browserify:
            main:
                files:
                    '<%= assetsPath %>/js/app.js': ['<%= resourcesPath %>/coffee/app.coffee']
                options:
                    transform: ['coffeeify']
                    browserifyOptions:
                        extensions: ['.coffee']
        # Compiles SASS to CSS
        sass:
            options:
                sourceMap: true
                implementation: sass
                includePaths: ['node_modules']
            dist:
                files:
                    'web/css/main.css': '<%= resourcesPath %>/sass/main.sass'
        # Watches for changes to SASS files and triggers compilation
        watch:
            sass:
                files: ['<%= resourcesPath %>/sass/**/*.{sass,scss}']
                tasks: ['sass']
            js:
                files: ['<%= resourcesPath %>/coffee/**/*.coffee']
                tasks: ['browserify']
            angularTemplates:
                files: ['<%= resourcesPath %>/views/frontend/**/*.html']
                tasks: ['copy:appTemplates']
            svgSprite:
                files: ['<%= resourcesPath %>/images/icons/*.svg']
                tasks: ['svgstore']
        # Watches for changes to CSS and reloads page in browser
        browserSync:
            bsFiles:
                src : ['<%= assetsPath %>/css/*.css']
            options:
                watchTask: true,
                debugInfo: true,
                proxy: 'localhost:8080'
                open: false
        # Generates SVG sprite from single files
        svgstore:
            options:
                prefix : 'icon-'
                svg:
                    viewBox : '0 0 100 100'
                    xmlns: 'http://www.w3.org/2000/svg'
            default:
                files:
                    'web/images/sprite.svg': ['<%= resourcesPath %>/images/icons/*.svg']
        # Runs clientside unit tests with Karma
        karma:
            options:
                frameworks: ['jasmine', 'browserify']
                files: [
                    'app/Resources/coffee/app.coffee'
                    'node_modules/angular-mocks/angular-mocks.js'
                    'test/*Spec.coffee'
                ]
                preprocessors:
                    'app/Resources/coffee/app.coffee': ['browserify']
                    'test/*Spec.coffee': ['browserify']

                browserify:
                    debug: yes
                    transform: ['coffeeify']
                    extensions: ['.coffee']
                    bundleDelay: 1000

                reporters: ['spec']
                browsers: ['PhantomJS']
            watch:
                autoWatch: yes
            once:
                singleRun: yes

    grunt.loadNpmTasks 'grunt-contrib-clean'
    grunt.loadNpmTasks 'grunt-contrib-copy'
    grunt.loadNpmTasks 'grunt-sass'
    grunt.loadNpmTasks 'grunt-contrib-watch'
    grunt.loadNpmTasks 'grunt-browser-sync'
    grunt.loadNpmTasks 'grunt-svgstore'
    grunt.loadNpmTasks 'grunt-browserify'
    grunt.loadNpmTasks 'grunt-karma'

    grunt.registerTask 'live', ['browserSync', 'watch']
    grunt.registerTask 'test', ['karma:once']
    grunt.registerTask 'default', ['clean', 'copy', 'browserify', 'sass', 'svgstore']
