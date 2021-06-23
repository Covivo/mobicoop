<template>
  <div>
    <v-row
      id="pad"
    >
      <v-col
        cols="2"        
        class="d-flex section justify-center align-center text-center text-uppercase text-body-2 pa-1"
      >
        <a
          :href="$t('pad.carpool.link')"
          :alt="$t('pad.carpool.title')"
          target="_blank"
        >{{ $t('pad.carpool.title') }}</a>
      </v-col>
      <v-col
        cols="2"        
        class="section justify-center align-center text-center text-uppercase text-body-2 pa-1"
      >
        <a
          :href="$t('pad.mobility.link')"
          :alt="$t('pad.mobility.title')"
          target="_blank"
        >{{ $t('pad.mobility.title') }}</a><br>
        <a
          :href="$t('pad.mobility.link')"
          :alt="$t('pad.mobility.title')"
          target="_blank"
          class="font-italic text-lowercase"
        >{{ $t('pad.mobility.subtitle') }}</a>
      </v-col>
      <v-col
        cols="2"        
        class="d-flex section justify-center align-center text-center text-uppercase text-body-2 pa-1"
      >
        <a
          :href="$t('pad.events.link')"
          :alt="$t('pad.events.title')"
          target="_blank"
        >{{ $t('pad.events.title') }}</a>
      </v-col>
      <v-col
        cols="2"
        class="d-flex section justify-center align-center text-center text-uppercase text-body-2 pa-1"
      >
        <a
          :href="$t('pad.subscribe.link')"
          :alt="$t('pad.subscribe.title')"
          target="_blank"
        >{{ $t('pad.subscribe.title') }}</a>
      </v-col>
      <v-col
        cols="2"        
        class="d-flex section justify-center align-center text-center text-uppercase text-body-2 pa-1"
      >
        <a
          :href="$t('pad.blog.link')"
          :alt="$t('pad.blog.title')"
          target="_blank"
        >{{ $t('pad.blog.title') }}</a>
      </v-col>
      <v-col
        cols="2"        
        class="d-lg-flex social justify-center align-center text-center text-center text-uppercase text-body-2 pa-1 justify-spacebetween"
      >
        <a
          :href="$t('pad.social.facebook.link')"
          :alt="$t('pad.social.facebook.title')"
          target="_blank"
        >
          <v-icon class="white--text mx-2">
            mdi-facebook
          </v-icon></a>
        <a
          :href="$t('pad.social.facebook.link')"
          :alt="$t('pad.social.facebook.title')"
          target="_blank"
        >
          <v-icon class="white--text mx-2">
            mdi-twitter
          </v-icon>
        </a>
        <a
          :href="$t('pad.social.facebook.link')"
          :alt="$t('pad.social.facebook.title')"
          target="_blank"
        >
          <v-icon class="white--text mx-2">
            mdi-linkedin
          </v-icon>
        </a>
      </v-col>      
    </v-row>
    <v-toolbar
      flat
      color="primary"
      height="80px"
    >
      <v-toolbar-title align="midle">
        <a
          href="/"
          class="d-flex align-center"
        >
          <img
            class="logo"
            :src="imageLink + 'MOBICOOP_LOGO-V1 Blanc.svg'"
            alt="Mobicoop"
            height="50"
            width="210"
            contain
            eager
          >
        </a>
      </v-toolbar-title>

      <v-spacer />

      <!--<accessibility />-->
      <div
        v-if="user"
        class="hidden-md-and-down"
      >
        <MMessageBtn :unread-message-number="unreadMessageNumber" />
        <MHeaderCommunities
          :user-id="user.id" 
          :text-color-class="textColorClass"
        />
        <MHeaderProfile
          :avatar="user.avatars[0]"
          :short-family-name="(user.shortFamilyName) ? user.givenName+' '+user.shortFamilyName : '-'"
          :show-reviews="showReviews"
          :text-color-class="textColorClass"
        />
      </div>
      <v-toolbar-items
        v-else
        class="hidden-md-and-down"
      >
        <v-btn
          class="white--text"
          rounded
          text
          :href="$t('buttons.signup.route')"
        >
          {{ $t('buttons.signup.label') }}
        </v-btn>
        <v-btn
          class="white--text"
          rounded
          text
          :href="$t('buttons.logIn.route')"
        >
          {{ $t('buttons.logIn.label') }}
        </v-btn>
      </v-toolbar-items>
      <v-btn
        v-if="user || publishButtonAlwaysActive==true"
        rounded
        color="secondary"
        :href="$t('buttons.shareAnAd.route')"
        class="hidden-md-and-down white--text mr-4"
        small
      >
        {{ $t('buttons.shareAnAd.label') }}
      </v-btn>
      <!-- <div @click="snackbar = true">
        <v-btn
          v-if="!user && publishButtonAlwaysActive==false"
          rounded
          disabled
          class="hidden-md-and-down"
        >
          {{ $t('buttons.shareAnAd.label') }}
        </v-btn>
      </div> -->
      <v-btn
        rounded
        color="secondary"
        :href="$t('buttons.solidary.route')"
        class="hidden-md-and-down white--text mr-4"
        small
      >
        {{ $t('buttons.solidary.label') }}
      </v-btn>
 
      <MHeaderLanguage
        :languages="languages"
        :language="dlocale"
        class="hidden-md-and-down"
        @languageSelected="updateLanguage"
      />
      <v-snackbar
        v-if="!user"
        v-model="snackbar"
        top
        color="info"
      >
        {{ $t('snackbar.needConnection') }}
        <v-btn
          color="info"
          icon
          elevation="0"
          @click="snackbar = false"
        >
          <v-icon
            color="primary"
          >
            mdi-close
          </v-icon>
        </v-btn>
      </v-snackbar>


      <!--Display menu when user is connected-->
      <v-menu
        v-if="user"
        bottom
        left
        z-index="9"
      >
        <template v-slot:activator="{ on }">
          <v-btn
            icon
            class="hidden-lg-and-up"
            v-on="on"
          >
            <v-icon>mdi-menu</v-icon>
          </v-btn>
        </template>

        <v-list>
          <v-list-item>
            <v-list-item-title>
              <MHeaderLanguage
                :languages="languages"
                :language="dlocale"
                @languageSelected="updateLanguage"
              />
            </v-list-item-title>
          </v-list-item>
          <v-list-item>
            <v-list-item-title>
              <MHeaderProfile
                :avatar="user.avatars[0]"
                :short-family-name="(user.shortFamilyName) ? user.givenName+' '+user.shortFamilyName : '-'"
                :show-reviews="showReviews"
                :text-color-class="textColorClass"
              />
            </v-list-item-title>
          </v-list-item>
          <v-list-item>
            <v-list-item-title>
              <MHeaderCommunities
                :user-id="user.id"
              />
            </v-list-item-title>
          </v-list-item>
          <v-list-item>
            <v-list-item-title>
              <v-btn
                text
                rounded
                :disabled="!user"
                :href="$t('buttons.shareAnAd.route')"
              >
                {{ $t('buttons.shareAnAd.label') }}
              </v-btn>
            </v-list-item-title>
          </v-list-item>
          <v-list-item>
            <v-list-item-title>
              <v-btn
                text
                rounded
                :disabled="!user"
                :href="$t('buttons.solidary.route')"
              >
                {{ $t('buttons.solidary.label') }}
              </v-btn>
            </v-list-item-title>
          </v-list-item>
          <v-list-item>
            <v-list-item-title>
              <v-btn
                text
                rounded
                :href="$t('buttons.messages.route')"
              >
                {{ $t('buttons.messages.label') }}
              </v-btn>
            </v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>


      <!--Display menu when there is no connected user-->
      <v-menu
        v-else
        bottom
        left
        z-index="9"
      >
        <template v-slot:activator="{ on }">
          <v-btn
            icon
            class="hidden-lg-and-up"
            v-on="on"
          >
            <v-icon>mdi-menu</v-icon>
          </v-btn>
        </template>

        <v-list>
          <v-list-item>
            <v-list-item-title>
              <v-btn
                text
                rounded
                :href="$t('buttons.messages.route')"
              >
                {{ $t('buttons.messages.label') }}
              </v-btn>
            </v-list-item-title>
          </v-list-item>
          <v-list-item>
            <v-list-item-title>
              <v-btn
                rounded
                text
                :href="$t('buttons.signup.route')"
              >
                {{ $t('buttons.signup.label') }}
              </v-btn>
            </v-list-item-title>
          </v-list-item>
          <v-list-item>
            <v-list-item-title>
              <v-btn
                rounded
                text
                :href="$t('buttons.logIn.route')"
              >
                {{ $t('buttons.logIn.label') }}
              </v-btn>
            </v-list-item-title>
          </v-list-item>
          <v-list-item>
            <v-list-item-title>
              <v-btn
                text
                rounded
                :disabled="!user && publishButtonAlwaysActive==false"
                :href="$t('buttons.shareAnAd.route')"
              >
                {{ $t('buttons.shareAnAd.label') }}
              </v-btn>
            </v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>
    </v-toolbar>
  </div>
</template>

<script>
import { merge, has } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/base/MHeader/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/base/MHeader/";
//import Accessibility from "@components/utilities/Accessibility";
import MHeaderProfile from "@components/base/MHeaderProfile.vue";
import MHeaderCommunities from "@components/base/MHeaderCommunities.vue";
import MHeaderLanguage from "@components/base/MHeaderLanguage.vue";
import MMessageBtn from "@components/base/MMessageBtn.vue";


let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedNl = merge(messages_nl, messages_client_nl);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
let MessagesMergedEu = merge(messages_eu, messages_client_eu);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'nl': MessagesMergedNl,
      'fr': MessagesMergedFr,
      'eu': MessagesMergedEu
    }
  },
  components: {
    //Accessibility,
    MHeaderProfile,
    MHeaderCommunities,
    MHeaderLanguage,
    MMessageBtn
  },
  props: {
    user: {
      type: Object,
      default: null
    },
    urlMobile: {
      type: String,
      default: null
    },
    locale: {
      type: String,
      default: "fr"
    },
    showReviews: {
      type: Boolean,
      default: false
    },
    languages: {
      type: Object,
      default: () => {}
    },
    token: {
      type: String,
      default: ''
    },
    publishButtonAlwaysActive:{
      type: Boolean,
      default:false
    }
  },
  data () {
    return {
      snackbar: false,
      width: 0,
      defaultLocale: 'fr',
      dlocale: this.locale,
      imageLink: "/images/pages/home/",
      textColorClass: "white--text title text-none"
    }
  },
  computed:{
    unreadMessageNumber(){
      return this.user.unreadCarpoolMessageNumber + this.user.unreadDirectMessageNumber + this.user.unreadSolidaryMessageNumber; 
    }
  },
  mounted() {
    if (has(this.languages, this.locale)) {
      this.dlocale = this.locale;
    } else {
      this.dlocale = this.defaultLocale;
    }
  },
  created() {
    this.$root.token = this.token;
    if(this.user){
      localStorage.setItem('X-LOCALE',this.dlocale);
    }
    this.$root.$i18n.locale = this.dlocale;
  },
  methods:{
    updateLanguage(language) {
      this.$root.$i18n.locale = language
    },
  }
};
</script>
<style lang="scss" scoped>
#pad{
  background-color:#007B85;
  margin:0px;
  color:white;
  a{
    text-decoration: none;
    color:white;
    font-family: Poppins, sans-serif !important;
  }
  .section{
    border-right: 1px solid white;
    margin:0px;
    vertical-align: middle;
  }
  .social{
    margin:0px;
    padding:0px;
    vertical-align: middle;
  }
}
</style>