import app from '../app'

import Navbar from './navbar'
import DurationInput from './duration-input'

app
  .directive('ppNavbar', Navbar)
  .directive('durationInput', DurationInput)
