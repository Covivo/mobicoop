import SolidaryUserBeneficiaryIcon from '@material-ui/icons/Accessibility'

import ListGuesser from '@api-platform/admin/lib/ListGuesser'
import ShowGuesser from '@api-platform/admin/lib/ShowGuesser'

export default {
    options: {
        label: 'Demandeurs solidaires'
    },
    list: ListGuesser,
    show: ShowGuesser,
    icon: SolidaryUserBeneficiaryIcon
}

