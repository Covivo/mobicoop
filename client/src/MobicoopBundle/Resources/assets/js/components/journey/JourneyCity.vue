<template>
  <v-container>
    <v-row
      align="center"
      justify="center"
    >
      <!-- TITLE -->
      <v-col
        align="center"
        cols="10"
      >
        <h1>{{ $t('title') }}</h1>
        <h2>{{ $t('subtitle') }}</h2>
      </v-col> 
    </v-row>
    <v-row justify="center">
      <v-col
        cols="10"
      >
        <v-tabs
          v-model="tab"
          background-color="success"
          center-active
        >
          <v-tab
            v-for="(letter,index) in cities"
            :key="index"
          >
            {{ index }}
          </v-tab>
        </v-tabs>
        <v-tabs-items 
          v-model="tab"
        >
          <v-card
            class="mx-auto"
          >
            <v-tab-item
              v-for="(letter, index) in cities"
              :key="index"
            >
              <v-simple-table
                fixed-header
                height="500px"
              >
                <template v-slot:default>
                  <thead>
                    <tr>
                      <th />
                      <th class="text-left">
                        {{ $t('fromCity') }}
                      </th>
                      <th class="text-left">
                        {{ $t('toCity') }}
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="city in letter"
                      :key="city.city"
                    >
                      <td>{{ city.city }}</td>
                      <td><a :href="$t('routeFromCity', { city: city.sanitized })">{{ $t('journeyFromCity', { city: city.city }) }}</a></td>
                      <td><a :href="$t('routeToCity', { city: city.sanitized })">{{ $t('journeyToCity', { city: city.city }) }}</a></td>
                    </tr>
                  </tbody>
                </template>
              </v-simple-table>
            </v-tab-item>
          </v-card>
        </v-tabs-items>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/journey/JourneyCity/";

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
    cities: {
      type: Object,
      default: () => {}
    }
  },
  data () {
    return {
      tab: 0,
    };
  }
};
</script>