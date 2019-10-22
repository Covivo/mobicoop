// Only import vue js components you need on twig files

// BASE
import MHeader from '@components/base/MHeader'
import MFooter from '@components/base/MFooter'

// HOME
import Home from '@components/home/Home'

// CONTACT
import Contact from '@components/contact/Contact'

// CARPOOL
import AdPublish from '@components/carpool/publish/AdPublish'
import Matching from '@components/carpool/results/Matching'

// COMMUNITY
import CommunityList from "@components/community/CommunityList";
import CommunityHelp from "@components/community/CommunityHelp";
import Community from '@components/community/Community'
import CommunitySecuredSignIn from '@components/community/CommunitySecuredSignIn'
import CommunityCreate from "@components/community/CommunityCreate";

// USER
import Login from '@components/user/Login'
import Messages from '@components/user/mailbox/Messages'
import PasswordRecovery from '@components/user/PasswordRecovery'
import PasswordRecoveryUpdate from '@components/user/PasswordRecoveryUpdate'
import Profile from '@components/user/Profile'
import Signupform from '@components/user/Signupform'
import SignupValidation from '@components/user/SignupValidation'
import UpdateProfile from '@components/user/UpdateProfile'

export default {
  MHeader,
  MFooter,
  Home,
  Contact,
  AdPublish,
  Matching,
  CommunityList,
  CommunityHelp,
  Community,
  CommunitySecuredSignIn,
  CommunityCreate,
  Login,
  Messages,
  PasswordRecovery,
  PasswordRecoveryUpdate,
  Profile,
  Signupform,
  SignupValidation,
  UpdateProfile
}