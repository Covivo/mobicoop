import NoteIcon from '@material-ui/icons/Note';

import { ArticleList } from './ArticleList';
import { ArticleShow } from './ArticleShow';
import { ArticleCreate } from './ArticleCreate';
import { ArticleEdit } from './ArticleEdit';

export default {
    options: {
        label: 'Articles'
    },
    list: ArticleList,
    show: ArticleShow,
    create: ArticleCreate,
    edit: ArticleEdit, 
    icon: NoteIcon
};