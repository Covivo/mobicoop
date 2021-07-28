<template>
  <v-row justify="center">
    <v-dialog
      v-if="badges.length > 0"
      v-model="dialog"
      persistent
      transition="dialog-bottom-transition"
      max-width="600"
    >
      <v-card
        v-for="badge in badges"
        :key="badge.id"
        max-width="600"
      >
        <v-row>
          <v-col cols="7">
            <v-card-title
              text-h5
              mb-1
            >
              {{ $t("wow") }}
            </v-card-title>
            <v-card-subtitle>{{ $t("youWonABadge") }} <b>{{ $t(badge.text) }}</b></v-card-subtitle>
          </v-col>
          <v-col cols="5">
            <v-img
              height="150"
              contain
              :src="badge.pictures.icon"
            />
          </v-col>
        </v-row>

        <v-card-text>
          <v-row
            align="center"
          >
            <v-img
              height="350"
              :src="badge.pictures.image"
            />
          </v-row>
          <v-row
            align="center"
            justify="center"
          >
            <div class="my-4 text-subtitle-1 text-center">
              {{ $t(badge.title) }}
            </div>
          </v-row>
          <v-row
            align="center"
            justify="center"
            class="mt-4"
          >
            <v-tooltip
              color="info"
              right
            >
              <template v-slot:activator="{ on }">
                <v-div v-on="on">
                  <v-btn
                    rounded
                    color="primary"
                    disabled
                  >
                    {{ $t("button.label") }}
                  </v-btn>
                </v-div>
              </template>
              <span>Développement à venir</span>
            </v-tooltip>
          </v-row> 
          <v-row
            align="center"
            justify="center"
          >
            <v-tooltip
              color="info"
              right
            >
              <template v-slot:activator="{ on }">
                <v-div v-on="on">
                  <v-checkbox
                    v-model="checkbox"
                    disabled
                    :label="$t('checkbox.label')"
                  />
                </v-div>
              </template>
              <span>Développement à venir</span>
            </v-tooltip>
          </v-row>
        </v-card-text>
        <v-card-actions class="justify-end">
          <v-btn
            rounded
            color="error"
            @click="dialog = false"
          >
            {{ $t("close") }}
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
      checkbox: false,
      dialog: true,
    }
  }
}
</script>