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
import CommunityWidget from "@components/community/CommunityWidget";
import CommunityGetWidget from "@components/community/CommunityGetWidget";

// USER
import Login from '@components/user/Login'
import LoginAdmin from '@components/user/LoginAdmin'
import Messages from '@components/user/mailbox/Messages'
import PasswordRecovery from '@components/user/PasswordRecovery'
import PasswordRecoveryUpdate from '@components/user/PasswordRecoveryUpdate'
import Profile from '@components/user/profile/Profile'
import Signupform from '@components/user/Signupform'
import SignupValidation from '@components/user/SignupValidation'
import UpdateProfile from '@components/user/profile/UpdateProfile'
import SsoLoginReturn from '@components/user/SsoLoginReturn'

// SOLIDARITY
import Solidary from '@components/solidary/Solidary'

// EVENT
import EventList from "@components/event/EventList";
import EventCreate from "@components/event/EventCreate";
import Event from "@components/event/Event";
import EventWidget from "@components/event/EventWidget";
import EventGetWidget from "@components/event/EventGetWidget";

// ARTICLE
import MArticle from "@components/article/MArticle";

// PLATFORM WIDGET
import PlatformWidget from "@components/utilities/platformWidget/PlatformWidget";
import PlatformGetWidget from "@components/utilities/platformWidget/PlatformGetWidget";

// BLOG POST
import BlogPost from "@components/utilities/blogPost/BlogPost";

// RELAY POINT
import RelayPoints from "@components/relaypoints/RelayPoints";

// TOOLBOX
import ToolBox from "@components/utilities/ToolBox/ToolBox";

// UTILITIES
import DayListChips from "@components/utilities/DayListChips";

// PAYMENT
import Payment from "@components/payment/Payment"
import PaymentPaid from "@components/user/profile/payment/PaymentPaid"

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
  CommunityWidget,
  CommunityGetWidget,
  Login,
  LoginAdmin,
  Messages,
  PasswordRecovery,
  PasswordRecoveryUpdate,
  Profile,
  Signupform,
  SignupValidation,
  UpdateProfile,
  Solidary,
  EventList,
  EventCreate,
  Event,
  EventWidget,
  EventGetWidget,
  MArticle,
  PlatformWidget,
  PlatformGetWidget,
  BlogPost,
  RelayPoints,
  ToolBox,
  DayListChips,
  Payment,
  PaymentPaid,
  SsoLoginReturn
}