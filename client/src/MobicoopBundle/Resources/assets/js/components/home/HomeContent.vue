<template>
  <v-row
    justify="center"
  >
    <!-- solidary -->
    <v-col
      v-if="solidaryDisplay"
      cols="8"
      class="text-right"
    >
      <h2 class="success--text font-italic">
        Actualités
      </h2>
      <p class="success--text text-h4 font-weight-black">
        Vous êtes solidaire ? Engagez-vous !
      </p>
      <p>
        Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortisconsequat.
        Duis autem vel eum iriure dolor in hendrerit, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto.
      </p>
      <v-btn
        rounded
        color="secondary"
        :href="this.$t('solidary.button.route')"
        class="hidden-md-and-down white--text"
      >
        {{ $t('solidary.button.label') }}
      </v-btn>
    </v-col>
    <v-col cols="4">
      <v-img
        :src="imageLink + 'solidaire.png'"
        class="align-self-center"
        cover
      />
    </v-col>
    <!-- end solidary -->

    <!-- features -->
    <m-features
      :title="$t('title')"
      :subtitle="$t('subtitle')"
      :items="items"
    />
    <!-- end features -->
    
    <!-- events -->

    <v-col
      cols="6"
      class="text-right"
    >
      <p class="success--text text-h4 font-weight-black">
        Allez en covoiturage à vos prochains événements
      </p>
    </v-col>
    <v-spacer />
    <!-- end events -->

    <!-- next carpools -->

    <v-col
      cols="12"
    >
      <p>Les prochains trajets</p>
    </v-col>
    <!-- end next carpools -->

    <!-- cooperative -->
    <v-col
      cols="6"
      class="text-right"
    >
      <p class="success--text text-h4 font-weight-black">
        {{ $t('cooperative.title') }}
      </p>
      <p>{{ $t('cooperative.text') }}</p>
    </v-col>
    <v-col cols="6">
      <v-img
        :src="imageLink + 'cooperative.png'"
        class="align-self-center"
        contain
      />
    </v-col>
    <v-col
      cols="12"
    >
      <v-btn
        rounded
        color="secondary"
        :href="this.$t('cooperative.button1.route')"
        class="hidden-md-and-down white--text"
        width="400px"
      >
        {{ $t('cooperative.button1.label') }}
      </v-btn>
      <v-btn
        rounded
        color="secondary"
        :href="this.$t('cooperative.button2.route')"
        class="hidden-md-and-down white--text"
        width="400px"
      >
        {{ $t('cooperative.button2.label') }}
      </v-btn>
    </v-col>
    <!-- end cooperative -->

    <!-- news -->
    <v-col
      cols="12"
    >
      <p class="success--text text-h4 font-weight-black">
        Quoi de neuf chez Mobicoop ?
      </p>

      <m-rss-articles
        :articles="articles"
      />
    </v-col>
    <!-- end news -->
  </v-row>
</template>
<script>

import { merge } from "lodash";
import MFeatures from "@components/utilities/MFeatures";
import MRssArticles from "@components/utilities/rssArticle/MRssArticles";
import {messages_en, messages_fr} from "@translations/components/home/HomeContent/";
import {messages_client_en, messages_client_fr} from "@clientTranslations/components/home/HomeContent/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
  },
  components: {
    MFeatures,
    MRssArticles
  },
  props: {
    solidaryDisplay: {
      type: Boolean,
      default: false
    },
    eventDisplay: {
      type: Boolean,
      default: false
    },
    communityDisplay: {
      type: Boolean,
      default: false
    },
    urlMobile: {
      type: String,
      default: null
    },
    articles: {
      type: Array,
      default: null
    }
  },
  data () {
    return {
      mobileUrl: this.urlMobile,
      imageLink: "/images/pages/home/",
      items:this.$t("items")
    }
  }
}
</script>