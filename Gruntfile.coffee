module.exports = (grunt) ->
    # Project configuration.
    grunt.initConfig
        resourcesPath: "app/Resources"
        npmPath: "node_modules"
        assetsPath: "web"

        # Cleans up folders we will later copy to
        clean: [
            "<%= assetsPath %>/partials"
            "<%= assetsPath %>/css"
            "<%= assetsPath %>/images"
            "<%= assetsPath %>/js"
            "<%= assetsPath %>/fonts"
        ]

        # Copies stuff that needs to be copied
        copy:
            appTemplates:
                expand: true
                cwd: "<%= resourcesPath %>/views/frontend"
                src: ["**/*.html"]
                dest: "<%= assetsPath %>/partials"
            bootstrapFonts:
                expand: true
                cwd: "<%= npmPath %>/bootstrap-sass/assets/fonts/bootstrap/"
                src: "*"
                dest: "<%= assetsPath %>/fonts"
            angularMaterialIconSets:
                expand: true
                cwd: "<%= resourcesPath %>/icons"
                src: ["**/*.svg"]
                dest: "<%= assetsPath %>/images/icons"
        # Compiles JS from coffee, enables Node.js module support
        browserify:
            main:
                files:
                    "<%= assetsPath %>/js/app.js": ["<%= resourcesPath %>/coffee/app.coffee"]
                options:
                    transform: ["coffeeify"]
                    browserifyOptions:
                        extensions: [".coffee"]
        # Compiles SASS to CSS
        sass:
            options:
                sourceMap: true
                includePaths: ["node_modules"]
            dist:
                files:
                    "web/css/main.css": "<%= resourcesPath %>/sass/main.sass"
        # Watches for changes to SASS files and triggers compilation
        watch:
            sass:
                files: ["<%= resourcesPath %>/sass/**/*.{sass,scss}"]
                tasks: ["sass"]
            js:
                files: ["<%= resourcesPath %>/coffee/**/*.coffee"]
                tasks: ["browserify"]
            angularTemplates:
                files: ["<%= resourcesPath %>/views/frontend/**/*.html"]
                tasks: ["copy:appTemplates"]
            svgSprite:
                files: ["<%= resourcesPath %>/images/icons/*.svg"]
                tasks: ["svgstore"]
        # Watches for changes to CSS and reloads page in browser
        browserSync:
            dev:
                bsFiles:
                    src : ["<%= assetsPath %>/css/*.css"]
                options:
                    watchTask: true,
                    debugInfo: true,
                    proxy: "picapica.dev"
                    open: false
        # Generates SVG sprite from single files
        svgstore:
            options:
                prefix : "icon-"
                svg:
                    viewBox : "0 0 100 100"
                    xmlns: "http://www.w3.org/2000/svg"
            default:
                files:
                    "web/images/sprite.svg": ["<%= resourcesPath %>/images/icons/*.svg"]

    grunt.loadNpmTasks "grunt-contrib-clean"
    grunt.loadNpmTasks "grunt-contrib-copy"
    grunt.loadNpmTasks "grunt-sass"
    grunt.loadNpmTasks "grunt-contrib-watch"
    grunt.loadNpmTasks "grunt-browser-sync"
    grunt.loadNpmTasks "grunt-svgstore"
    grunt.loadNpmTasks "grunt-browserify"

    grunt.registerTask "live", ["browserSync", "watch"]
    grunt.registerTask "default", ["clean", "copy", "browserify", "sass", "svgstore"]
