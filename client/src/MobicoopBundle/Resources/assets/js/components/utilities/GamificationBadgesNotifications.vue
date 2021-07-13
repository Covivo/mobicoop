<template>
  <v-row justify="center">
    <v-dialog
      v-if="badges.lenght > 0"
      v-model="dialog"
      persistent
      transition="dialog-bottom-transition"
      max-width="500"
    >
      <v-card
        v-for="badge in badges"
        :key="badge.id"
        max-width="500"
      >
        <v-row>
          <v-col cols="7">
            <v-card-title
              text-h5
              mb-1
            >
              WAHOU!!!
            </v-card-title>
            <v-card-subtitle>Vous venez de gagner le badge <b>{{ badge.text }}</b></v-card-subtitle>
          </v-col>
          <v-col cols="5">
            <v-img
              height="150"
              :src="badge.pictures.icon"
            />
          </v-col>
        </v-row>

        <v-card-text>
          <v-row
            align="center"
          >
            <v-img
              height="250"
              :src="badge.pictures.image"
            />
          </v-row>
          <v-row
            align="center"
          >
            <div class="my-4 text-subtitle-1 text-center">
              {{ badge.title }}
            </div>
          </v-row>
          <v-row
            align="center"
            justify="center"
          >
            <div class="text-center">
              Small plates, salads & sandwiches - an intimate setting with 12 indoor seats plus patio seating.
            </div>
          </v-row>
          <v-row
            align="center"
            justify="center"
            class="mt-4"
          >
            <v-btn>Voir "Badges"</v-btn>
          </v-row> 
          <v-row
            align="center"
            justify="center"
          >
            <v-checkbox
              v-model="checkbox"
              label="Ne plus afficher de messages de ce type"
            />
          </v-row>
        </v-card-text>
        <v-card-actions class="justify-end">
          <v-btn
            text
            @click="dialog = false"
          >
            Fermer
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-row>
</template>
<script>
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/gamification/GamificationBadgesNotifications/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    },
  },
  props: {
    badges: {
      type: Array,
      default: null
    }
  },
  data () {
    return {
      badgeName: "nom du badge",
      checkbox: false,
      dialog: true
    }
  }
}
</script>