


                <?php 
                    $media = getMedia($featuredReview['postID'], $conn);
                    if ($media): ?>
                    <section class="review-media">
                        <?php if ($media['typeMedia'] === 'Images'): ?>
                            <img src="<?= htmlspecialchars($media['location']) ?>" alt="Review Image" class="media-item">
                        <?php elseif ($media['typeMedia'] === 'Video'): ?>
                            <video controls class="media-item">
                                <source src="<?= htmlspecialchars($media['location']) ?>" type="video/mp4">
                            </video>
                        <?php elseif ($media['typeMedia'] === 'Audio'): ?>
                            <audio controls class="media-item">
                                <source src="<?= htmlspecialchars($media['location']) ?>" type="audio/mpeg">
                            </audio>
                        <?php endif; ?>
                    </section>
                    <?php endif; ?>
