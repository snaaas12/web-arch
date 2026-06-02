import React from 'react'
import ReactDOM from 'react-dom/client'

import Comments from './components/Comments'

const commentsRoot = document.getElementById('comments-root')

if (commentsRoot) {

    const postId =
        commentsRoot.dataset.postId

    const userName =
        commentsRoot.datasetUserName

    ReactDOM.createRoot(commentsRoot).render(
        <React.StrictMode>
            <Comments
                postId={postId}
                userName={userName}
            />
        </React.StrictMode>
    )
}
