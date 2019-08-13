<template>
  <v-container
    grid-list-md
    text-xs-center
  >
    <!-- Punctual -->
    <!-- First row -->
    <v-layout
      row
      wrap
      align-center
      justify-center
    >
      <v-flex
        xs6
        offset-xs2
      >
        <v-menu
          v-model="menuOutwardDate"
          :close-on-content-click="false"
          :nudge-right="40"
          lazy
          transition="scale-transition"
          offset-y
          full-width
          min-width="290px"
        >
          <template v-slot:activator="{ on }">
            <v-text-field
              v-model="internalOutwardDate"
              :label="$t('outwardDate.label')"
              prepend-icon=""
              readonly
              v-on="on"
            />
          </template>
          <v-date-picker
            v-model="internalOutwardDate"
            @input="menuOutwardDate = false"
            @change="change"
          />
        </v-menu>
      </v-flex>

      <v-flex
        xs4
      >
        <v-menu
          ref="menu"
          v-model="menuOutwardTime"
          :close-on-content-click="false"
          :nudge-right="40"
          :return-value.sync="outwardTime"
          lazy
          transition="scale-transition"
          offset-y
          full-width
          max-width="290px"
          min-width="290px"
        >
          <template v-slot:activator="{ on }">
            <v-text-field
              v-model="outwardTime"
              :label="$t('outwardTime.label')"
              prepend-icon=""
              readonly
              v-on="on"
            />
          </template>
          <v-time-picker
            v-if="menuOutwardTime"
            v-model="outwardTime"
            format="24hr"
            @click:minute="$refs.menu.save(outwardTime)"
            @change="change"
          />
        </v-menu>
      </v-flex>
    </v-layout>

    <!-- Second row -->
    <v-layout
      row
      wrap
      align-center
      justify-center
    >
      <v-flex
        xs2
      >
        <v-checkbox
          v-model="returnTrip"
          :label="$t('returnTrip.label')"
          color="success"
          hide-details
          @change="change"
        />
      </v-flex>

      <v-flex
        xs6
      >
        <v-menu
          v-model="menuReturnDate"
          :close-on-content-click="false"
          :nudge-right="40"
          lazy
          transition="scale-transition"
          offset-y
          full-width
          min-width="290px"
        >
          <template v-slot:activator="{ on }">
            <v-text-field
              v-model="returnDate"
              :label="$t('returnDate.label')"
              prepend-icon=""
              readonly
              v-on="on"
            />
          </template>
          <v-date-picker
            v-model="returnDate"
            @input="menuReturnDate = false"
            @change="change"
          />
        </v-menu>
      </v-flex>

      <v-flex
        xs4
      >
        <v-menu
          ref="menu"
          v-model="menuReturnTime"
          :close-on-content-click="false"
          :nudge-right="40"
          :return-value.sync="returnTime"
          lazy
          transition="scale-transition"
          offset-y
          full-width
          max-width="290px"
          min-width="290px"
        >
          <template v-slot:activator="{ on }">
            <v-text-field
              v-model="returnTime"
              :label="$t('returnTime.label')"
              prepend-icon=""
              readonly
              v-on="on"
            />
          </template>
          <v-time-picker
            v-if="menuReturnTime"
            v-model="returnTime"
            format="24hr"
            @click:minute="$refs.menu.save(returnTime)"
            @change="change"
          />
        </v-menu>
      </v-flex>
    </v-layout>

    <!-- Regular -->
  </v-container>
</template>

<script>
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/carpool/AdPlanification.json";
import TranslationsClient from "@clientTranslations/components/carpool/AdPlanification.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  components: {
  },
  props: {
    outwardDate: {
      type: String,
      default: null
    },
    regular: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      internalOutwardDate: this.outwardDate,
      outwardTime: null,
      returnDate: null,
      returnTime: null,
      menuOutwardDate: false,
      menuOutwardTime: false,
      menuReturnDate: false,
      menuReturnTime: false,
      returnTrip: false
    };
  },
  computed: {
  },
  updated() {
    this.internalOutwardDate = this.outwardDate;
  },
  methods: {
    change() {
      this.$emit("change", {
        outwardDate: this.internalOutwardDate,
        outwardTime: this.outwardTime,
        returnDate: this.returnDate,
        returnTime: this.returnTime,
        returnTrip: this.returnTrip
      });
    }
  }
};
</script>