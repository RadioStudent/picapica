module.exports = function(grunt) {

    // Project configuration.
    grunt.initConfig({
        resourcesPath: 'app/Resources',
        bowerPath: 'bower_components',
        assetsPath: 'web',

        // Compiles SASS to CSS
        sass: {
            dist: {
                options: {
                    bundleExec: true,
                    style: 'expanded',
                    require: ['sass-globbing'],
                    loadPath: 'bower_components'
                },
                files: [{
                    expand: true,
                    cwd: '<%= resourcesPath %>/sass',
                    src: ['*.sass'],
                    dest: 'web/css',
                    ext: '.css'
                }],
            },
        },
        // Watches for changes to SASS files and triggers compilation
        watch: {
            sass: {
                files: ['<%= resourcesPath %>/sass/**/*.{sass,scss}'],
                tasks: ['sass']
            },
            js: {
                files: ['<%= resourcesPath %>/js/**/*.js'],
                //tasks: ['copy:websiteBundle']
                tasks: ['browserify']
            },
            svgSprite: {
                files: ['<%= resourcesPath %>/images/icons/*.svg'],
                tasks: ['svgstore']
            }
        },
        // Watches for changes to CSS and reloads page in browser
        browserSync: {
            dev: {
                bsFiles: {
                    src : ['<%= assetsPath %>/css/*.css']
                },
                options: {
                    watchTask: true,
                    debugInfo: true,
                    proxy: 'picapica.dev'
                }
            },
        },
        // Generates SVG sprite from single files
        svgstore: {
            options: {
                prefix : 'icon-',
                svg: {
                    viewBox : '0 0 100 100',
                    xmlns: 'http://www.w3.org/2000/svg'
                }
            },
            default: {
                files: {
                    'web/images/sprite.svg': ['<%= resourcesPath %>/images/icons/*.svg'],
                }
            },
        },
        browserify: {
            main: {
                files: {
                    'web/js/main.js': ['<%= resourcesPath %>/js/**/*.js'],
                }
            }
        }


    });

    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-browser-sync');
    grunt.loadNpmTasks('grunt-svgstore');
    grunt.loadNpmTasks('grunt-browserify');

    grunt.registerTask('live', ['browserSync', 'watch']);
    grunt.registerTask('default', ['sass', 'svgstore', 'browserify']);
};