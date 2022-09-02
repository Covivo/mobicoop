<template>
  <v-footer
    height="auto"
    padless
    tile
    color="primary"
  >
    <v-container
      fluid
      class="pa-0"
    >
      <v-row
        justify="center"
      >
        <a
          href="/"
          class="d-flex align-center my-14"
        >
          <v-img
            class="logo"
            :src="imageLink + 'MOBICOOP_LOGO-V1 Blanc.svg'"
            :alt="$t('logo')"
            height="50"
            width="280"
            contain
            eager
          />
        </a>
      </v-row>
      <v-row
        justify="center"
        dense
        class="pa-5"
      >
        <!-- first col -->
        <v-col cols="3">
          <v-card-text class="my-0 py-0 white--text font-weight-bold">
            <span>{{ $t('category.cooperative') }}<span /></span>
          </v-card-text>
          <v-card-text class="my-0 py-1">
            <a
              :href="$t('cooperative.talkAboutUs.link')"
              style="text-decoration:none;"
              class="white--text"
              target="_blank"
            >
              {{ $t('cooperative.talkAboutUs.title') }}
            </a>
          </v-card-text>
          <v-card-text class="my-0 py-0 white--text">
            <a
              :href="$t('cooperative.joinUs.link')"
              style="text-decoration:none;"
              class="white--text"
              target="_blank"
            >
              {{ $t('cooperative.joinUs.title') }}
            </a>
          </v-card-text>
          <v-card-text class="my-0 py-1">
            <a
              :href="$t('cooperative.toolbox.link')"
              style="text-decoration:none;"
              class="white--text"
            >{{ $t('cooperative.toolbox.title') }}</a>
          </v-card-text>

          <v-card-text class="my-0 pt-10 pb-0 white--text font-weight-bold">
            <span>{{ $t('category.services') }}</span>
          </v-card-text>
          <v-card-text class="my-0 py-1">
            <a
              :href="$t('services.mobicoop.link')"
              style="text-decoration:none;"
              class="white--text"
              target="_blank"
            >
              {{ $t('services.mobicoop.title') }}
            </a>
          </v-card-text>
          <v-card-text class="my-0 py-1">
            <a
              :href="$t('services.covievent.link')"
              style="text-decoration:none;"
              class="white--text"
              target="_blank"
            >
              {{ $t('services.covievent.title') }}
            </a>
          </v-card-text>
        </v-col>
        <!-- end first col -->

        <!-- second col -->
        <v-col cols="3">
          <v-card-text class="my-0 py-0 white--text font-weight-bold">
            <span>{{ $t('category.tools') }}<span /></span>
          </v-card-text>
          <v-card-text class="my-0 py-1">
            <a
              :href="$t('tools.map.link')"
              style="text-decoration:none;"
              class="white--text"
            >{{ $t('tools.map.title') }}</a>
          </v-card-text>
          <v-card-text class="my-0 py-1">
            <a
              :href="$t('tools.events.link')"
              style="text-decoration:none;"
              class="white--text"
            >{{ $t('tools.events.title') }}</a>
          </v-card-text>
          <v-card-text class="my-0 py-1">
            <a
              :href="$t('tools.dataPolicy.link')"
              style="text-decoration:none;"
              class="white--text"
            >{{ $t('tools.dataPolicy.title') }}</a>
          </v-card-text>
          <v-card-text class="my-0 py-0 white--text text-h6">
            <a
              :href="$t('tools.faq.link')"
              style="text-decoration:none;"
              class="white--text"
            >{{ $t('tools.faq.title') }}</a>
          </v-card-text>
          <v-card-text class="my-0 py-1">
            <a
              :href="$t('tools.cgu.link')"
              style="text-decoration:none;"
              class="white--text"
            >{{ $t('tools.cgu.title') }}</a>
          </v-card-text>
          <v-card-text class="my-0 py-1">
            <a
              style="text-decoration:none;"
              class="white--text"
              @click="showCookiesAction()"
            >{{ $t('tools.cookies.title') }}</a>
          </v-card-text>

          <v-card-text class="my-0 pt-3 pb-0 white--text font-weight-bold">
            <span>{{ $t('category.contacts') }}</span>
          </v-card-text>
          <v-card-text class="my-0 py-1">
            <a
              :href="$t('contacts.hotline.link')"
              style="text-decoration:none;"
              class="white--text"
            >{{ $t('contacts.hotline.title') }}</a>
          </v-card-text>
          <v-card-text class="my-0 py-1">
            <a
              :href="$t('contacts.presse.link')"
              style="text-decoration:none;"
              class="white--text"
            >{{ $t('contacts.presse.title') }}</a>
          </v-card-text>
        </v-col>
        <!-- end second col -->

        <!-- third col -->
        <v-col cols="3">
          <v-card-text class="my-0 py-0 white--text font-weight-bold">
            <span>{{ $t('category.need') }}<span /></span>
          </v-card-text>
          <v-card-text class="my-0 py-1">
            <a
              :href="$t('need.community.link')"
              style="text-decoration:none;"
              class="white--text"
            >
              {{ $t('need.community.title') }}
            </a>
          </v-card-text>

          <v-card-text class="my-0 pt-10 pb-0 white--text subtitle font-weight-black">
            {{ $t('category.download') }}
          </v-card-text>
          <v-card-text class="my-0 py-0">
            <v-row>
              <a
                :href="$t('urlIos')"
                target="_blank"
              >
                <v-img
                  contain
                  :src="$t('urlIosLogo')"
                  max-width="200px"
                  class="ml-2"
                  :alt="$t('logoIosAlt')"
                />
              </a>
              <a
                :href="$t('urlAndroid')"
                target="_blank"
              >
                <v-img
                  contain
                  :src="$t('urlAndroidLogo')"
                  max-width="200px"
                  class="ml-2"
                  :alt="$t('logoAndroidAlt')"
                />
              </a>
            </v-row>
          </v-card-text>
        </v-col>
        <!-- end third col -->

        <!-- fourth col -->
        <v-col
          cols="1"
          justify="center"
        >
          <v-card-text class="my-0 py-0 white--text text-h6">
            <v-row justify="center">
              <a
                :href="$t('urlInstagram')"
                target="_blank"
              >
                <v-img
                  class="ml-2"
                  contain
                  :src="$t('urlInstagramLogo')"
                  max-width="75px"
                  :alt="$t('logoInstragramAlt')"
                />
              </a>
              <a
                :href="$t('urlFacebook')"
                target="_blank"
              >
                <v-img
                  contain
                  :src="$t('urlFacebookLogo')"
                  max-width="75px"
                  :alt="$t('logoFacebookAlt')"
                />
              </a>
              <a
                :href="$t('urlTwitter')"
                target="_blank"
              >
                <v-img
                  contain
                  :src="$t('urlTwitterLogo')"
                  max-width="75px"
                  :alt="$t('logoTwitterAlt')"
                />
              </a>
              <a
                :href="$t('urlFramasphere')"
                target="_blank"
              >
                <v-img
                  contain
                  :src="$t('urlFramasphereLogo')"
                  max-width="75px"
                  :alt="$t('logoFramasphereAlt')"
                />
              </a>
            </v-row>
          </v-card-text>
          <v-col>
            <!-- end col -->
          </v-col>
        </v-col>
      </v-row>
      <v-row
        class="my-4 white--text"
        text-center
        justify="center"
        align="center"
      >
        <strong>Mobicoop</strong> <v-chip
          class="ma-2 black--text"
        >
          {{ version }}
        </v-chip> by Mobicoop team. The source code is licensed AGPL3.
      </v-row>
      <cookies
        :show="showCookies"
        :app-name="appName"
        :auto-show="cookiesShowAuto"
        :social-networks-active="socialNetworksActive"
        :social-cookies="socialCookies"
        @dialogClosed="dialogCookiesClosed"
      />
    </v-container>
  </v-footer>
</template>

<script>
import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/base/MFooter/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/base/MFooter/";
import Cookies from "@components/utilities/Cookies";
import Package from '@root/package.json'

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
  components:{
    Cookies
  },
  props:{
    appName:{
      type: String,
      default:null
    },
    cookiesShowAuto:{
      type: Boolean,
      default: true
    },
    socialNetworksActive:{
      type: Boolean,
      default: false
    },
    socialCookies:{
      type: Array,
      default: null
    }
  },
  data() {
    return {
      version:Package.version,
      imageLink: "/images/pages/home/",
      showCookies: false
    }
  },
  methods:{
    showCookiesAction(){
      this.showCookies = true;
    },
    dialogCookiesClosed(){
      this.showCookies = false;
    }
  }
};
</script>


<style lang="scss" scoped>
  span{
    font-size: 1.3rem;
  }
  a{
    font-size: 1.1rem;
  }
</style>

