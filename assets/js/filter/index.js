import app from '../app'

import Capitalise from './capitalise'
import Duration from './duration'
import HasComment from './has-comment'
import Icon from './icon'

app
  .filter('capitalise', Capitalise)
  .filter('duration', Duration)
  .filter('hasComment', HasComment)
  .filter('icon', Icon)
